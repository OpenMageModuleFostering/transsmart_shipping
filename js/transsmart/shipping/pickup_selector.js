/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
if (!Transsmart) var Transsmart = { };
if (!Transsmart.Shipping) Transsmart.Shipping = { };

Transsmart.Shipping.Pickup = Class.create({

    /**
     * Define configuration for the pickup shipping method
     */
    config: {
        shippingMethodName: 'transsmartpickup',
        shippingPickupContainerId: 'tss-location-selector',
        shippingMethodLoadDivId: 'checkout-shipping-method-load',
        locationsListDivId: 'tss-ls-locations',
        googleMapsId: 'tss-ls-map',
        selectButtonId: 'tss-ls-select',
        closeButtonId: 'tss-ls-close',
        shippingPickupSelectButtonId: 'tss-ls-select-location',
        selectedLocationDivId: 'tss-ls-selected-location-info'
    },

    googleMaps: null,
    googleGeocoder: null,
    selectedShippingMethod: null,
    markers: [],
    infoWindow: null,
    selectedMarker: null,
    ajaxRequest: null,
    isLoading: false,
    isClosing: false,

    origShippingMethod: null,
    origPickupDivHtml: '',

    /**
     * Initialize the pickup location selector
     */
    initialize: function(config) {
        // Check whether the viewUrl is provided
        if (typeof config == 'undefined' || typeof config.lookupUrl == 'undefined') {
            Transsmart.Logger.log('Transsmart_Shipping: Missing lookupUrl in the supplied config');
            return;
        }

        // Set the view url
        this.config.lookupUrl = config.lookupUrl;

        // Check whether the carrierProfileIds is provided
        if (typeof config == 'undefined' || typeof config.carrierProfileIds == 'undefined') {
            Transsmart.Logger.log('Transsmart_Shipping: Missing carrierProfileIds in the supplied config');
            return;
        }

        this.config.carrierProfileIds = config.carrierProfileIds;

        this.attachListenHandler();
        this.attachFilterHandlers();
        this.attachSearchHandler();

        // attach window resize handler
        document.observe('dom:loaded', function() {
            Event.observe(window, 'resize', this.onWindowResize.bind(this));
        }.bind(this));

        // observe ajax update event
        Ajax.Responders.register({ onComplete: this.onAjaxComplete.bind(this) });
    },

    /**
     * Attaches the listen handler so that we know when we should be
     * showing the pickup selector popup.
     */
    attachListenHandler: function() {
        var self = this;

        // Attach click event to close button
        $(this.config.closeButtonId).observe('click', function(event) {
            self.close();
            event.stop();
        });

        // Wait for the dom to be ready
        document.observe('dom:loaded', function () {
            // Add our custom validator
            this.addValidators();

            if (typeof checkout.gotoSection == 'function') {
                // hook into Magento's OnePage checkout
                var parentGotoSection = checkout.gotoSection.bind(checkout);

                // Triggered when the checkout switches to the shipping method selection
                checkout.gotoSection = function (section, reloadProgressBlock) {
                    // Remove the progress block when we switch to a different section
                    if (['billing', 'shipping', 'shipping_method'].indexOf(section) !== -1) {
                        if ($('tss-ls-progress-pickup-header')) {
                            $('tss-ls-progress-pickup-header').remove();
                        }

                        if ($('tss-ls-progress-pickup-content')) {
                            $('tss-ls-progress-pickup-content').remove();
                        }
                    }

                    if (section == 'shipping_method') {
                        this.updateShippingMethods();
                    }

                    parentGotoSection(section, reloadProgressBlock);
                }.bind(this);
            }

            // The shipping methods are generated dynamically
            // So we need to observe for dom changes and then trigger the click event based on that
            $(document).on('click', 'input[name=shipping_method]', function (event, element) {
                // Skip shipping methods that do not match the shipping method name
                if (element.value && element.value.indexOf(self.config.shippingMethodName) == -1) {
                    this.removePickupDiv();
                    return;
                }

                this.selectedShippingMethod = element.value;
                this.attachPickupDiv(element);
            }.bind(this));

            this.updateShippingMethods();

            $(this.config.selectButtonId).observe('click', function (event) {
                this.selectLocationAndClose();
                event.stop();
            }.bind(this));
        }.bind(this));
    },

    onAjaxComplete: function () {
        this.updateShippingMethods();
    },

    updateShippingMethods: function () {
        // Check which shipping method has been selected
        var checkedShippingMethods = $$('input[name=shipping_method]:checked');

        if (checkedShippingMethods.length != 0) {
            // The pre-selected shipping method is the pickup one.
            // Attach the pickup div to the radio button
            if (checkedShippingMethods[0].value.indexOf(this.config.shippingMethodName) != -1) {
                this.selectedShippingMethod = checkedShippingMethods[0].value;
                this.attachPickupDiv(checkedShippingMethods[0]);
            }
        }
    },

    /**
     * The filter handlers determine which markers should be shown
     */
    attachFilterHandlers: function() {
        $('tss-ls-show-open-early').observe('click', this.updateMapMarkers.bind(this));
        $('tss-ls-show-open-late').observe('click', this.updateMapMarkers.bind(this));
        $('tss-ls-show-open-sunday').observe('click', this.updateMapMarkers.bind(this));
    },

    /**
     * Attaches the search handler
     */
    attachSearchHandler: function() {
        $('tss-ls-search-btn').observe('click', this.attachSearch.bind(this));
        $('tss-ls-search').observe('keypress', this.attachSearch.bind(this));
    },

    /**
     * Perform search
     * @param event
     */
    attachSearch: function (event) {
        if (event.type == 'keypress' && event.keyCode != Event.KEY_RETURN) {
            return;
        }

        var searchField  = $('tss-ls-search');
        var searchButton = $('tss-ls-search-btn');

        var searchValue = searchField.value;
        if (searchValue == '') {
            return;
        }

        searchField.disabled = true;
        searchField.addClassName('disabled');
        searchButton.disabled = true;
        searchButton.addClassName('disabled');

        this.retrieveLocation(searchValue, function (bounds) {
            searchField.disabled = false;
            searchField.removeClassName('disabled');
            searchButton.disabled = false;
            searchButton.removeClassName('disabled');

            this.updateMapMarkers();

            google.maps.event.trigger(this.googleMaps, 'resize');
            this.googleMaps.setCenter(bounds.getCenter());
            this.googleMaps.fitBounds(bounds);

        }.bind(this),
        function (error) {
            searchField.disabled = false;
            searchField.removeClassName('disabled');
            searchButton.disabled = false;
            searchButton.removeClassName('disabled');

            if (this.isClosing) {
                return;
            }
            Transsmart.Logger.log('Transsmart_Shipping error: ' + error);
            alert(Translator.translate('Search did not succeed. Please try again.'));

        }.bind(this));
    },

    /**
     * Attaches the pickup location selector div to the parent item of the input element.
     * @param inputElement The input radio element that's part of a carrier
     */
    attachPickupDiv: function(inputElement) {
        var containerItem = $(this.config.shippingPickupContainerId);

        // Remove the shipping pickup div if it exists
        if (containerItem) {
            containerItem.remove();
        }

        var valueBits = inputElement.value.match(/transsmartpickup_carrierprofile_([0-9]+)/);

        // Nothing to attach, it doesn't match
        if (valueBits == null || valueBits.length != 2) {
            return;
        }

        // Not a carrierprofile that has location selector enabled
        if (this.config.carrierProfileIds.indexOf(valueBits[1]) == -1) {
            Transsmart.Logger.log('Carrier profile with id: ' + valueBits[1] + ' does not allow location selector');
            Transsmart.Logger.log('Allowed location selectors are: ', this.config.carrierProfileIds);
            return;
        }

        // Find the container for transsmart pickup shipping method
        var shippingMethodContainer = this.getButtonContainer(inputElement);
        if (typeof shippingMethodContainer == 'undefined') {
            Transsmart.Logger.log('Transsmart.Shipping: ' +
                Translator.translate('Could not find shipping container for ' + this.config.shippingMethodName));
            return;
        }

        // insert the location selector button
        shippingMethodContainer.insert(this.getPickupDiv());
        $(this.config.shippingPickupSelectButtonId).observe('click', function(event) {
            this.showLocationPopUp();
            event.stop();
        }.bind(this));
    },

    /**
     * Find the container for transsmart pickup shipping method button
     *
     * @param inputElement
     */
    getButtonContainer: function (inputElement) {
        // Find the container for transsmart pickup shipping method
        var buttonContainer = inputElement.up('dd, div');
        return buttonContainer;
    },

    /**
     * Removes the pickup div element
     */
    removePickupDiv: function() {
        var containerItem = $(this.config.shippingPickupContainerId);

        if (containerItem != null) {
            containerItem.remove();
        }
    },

    /**
     * Returns the div that is used near the shipping radio button
     * @returns {string}
     */
    getPickupDiv: function() {
        if (this.origPickupDivHtml == '' || this.origShippingMethod != this.selectedShippingMethod) {
            this.origShippingMethod = this.selectedShippingMethod;
            this.origPickupDivHtml = '<div id="' + this.config.shippingPickupContainerId + '">' +
                '<button id="' + this.config.shippingPickupSelectButtonId + '" class="tss-ls-select-location button" onclick="return false">' +
                '<span><span>' +
                Translator.translate('Select pickup location') +
                '</span></span>' +
                '</button>' +
                '<div id="' + this.config.selectedLocationDivId + '"></div>' +
                '<input id="tss-ls-location-data" name="transsmart_pickup_address_data" type="hidden" class="validate-selected-location" />' +
            '</div>';
        }
        return this.origPickupDivHtml;
    },

    /**
     * Adds the required validators to the validation class.
     */
    addValidators: function() {
        Validation.add(
            'validate-selected-location',
            Translator.translate("A pickup location has to be selected"),
            function(v) {
                return !Validation.get('IsEmpty').test(v);
        });
    },

    /**
     * Shows the pickup location selector popup
     */
    showLocationPopUp: function() {
        var pickupContainer = $('tss-pickup-container');
        var pickupLoader = pickupContainer.select('.tss-loader')[0];
        var pickupContent = pickupContainer.select('.tss-selector-container')[0];

        this.isClosing = false;
        if (this.originalLoaderHtml) {
            pickupLoader.innerHTML = this.originalLoaderHtml;
        }
        else {
            this.originalLoaderHtml = pickupLoader.innerHTML;
        }

        // remove validation advice
        $$('#' + this.config.shippingPickupContainerId + ' .validation-advice').each(function(item) {
            new Effect.Fade(item);
        });

        // Reset the markers
        this.clearMapMarkers();

        // Reset input fields
        $('tss-ls-search').value = '';
        $('tss-ls-show-open-early').checked = false;
        $('tss-ls-show-open-late').checked = false;
        $('tss-ls-show-open-sunday').checked = false;

        pickupLoader.show();
        pickupContent.hide();
        this.isLoading = true;

        // Show the pickup container
        pickupContainer.appear({ duration: 0.3, afterSetup: this.onWindowResize.bind(this) });

        this.retrieveLocation(null, function(bounds) {
            pickupLoader.hide();
            pickupContent.show();
            this.isLoading = false;
            this.onWindowResize();

            this.googleMaps.setCenter(bounds.getCenter());
            this.googleMaps.fitBounds(bounds);

            try {
                var locationData = eval('(' + atob($('tss-ls-location-data').value) + ')');

                for (var i = 0; i < this.markers.length; i++) {
                    if (this.markers[i].locationData.servicepoint_id == locationData.servicepoint_id) {
                        this.selectedMarker = this.markers[i];
                        break;
                    }
                }
            }
            catch (e) {
            }

        }.bind(this), function (error) {
            if (this.isClosing) {
                return;
            }
            Transsmart.Logger.log('Transsmart_Shipping error: ' + error);
            pickupLoader.innerHTML = '<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>' +
                Translator.translate('Error loading pickup location selector. Please try again.') +
                '<p>&nbsp;</p><p>&nbsp;</p>';
        }.bind(this));
    },

    /**
     * Get the current shipping address if possible.
     * Needed for LightCheckout when the quote address is not updated via AJAX.
     */
    getCurrentAddress: function () {
        var result = {};

        try {
            // determine address prefix
            var addressPrefix;
            var billingUseForShipping1 = $('billing_use_for_shipping_yes');
            var billingUseForShipping2 = $('billing:use_for_shipping_yes');
            var shippingSameAsBilling  = $('shipping:same_as_billing');
            if (billingUseForShipping1) {
                addressPrefix = billingUseForShipping1.checked ? 'billing_' : 'shipping_';
            }
            else if (shippingSameAsBilling) {
                // if an address is selected in the address book, ignore the address fields
                var addressSelect = $((shippingSameAsBilling.checked ? 'billing' : 'shipping') + '-address-select');
                if (addressSelect && addressSelect.value) {
                    return result;
                }
                addressPrefix = shippingSameAsBilling.checked ? 'billing:' : 'shipping:';
            }
            else if (billingUseForShipping2) {
                // if an address is selected in the address book, ignore the address fields
                var addressSelect = $((billingUseForShipping2.checked ? 'billing' : 'shipping') + '-address-select');
                if (addressSelect && addressSelect.value) {
                    return result;
                }
                addressPrefix = billingUseForShipping2.checked ? 'billing:' : 'shipping:';
            }
            else if ($('shipping_postcode')) {
                addressPrefix = 'shipping_';
            }
            else if ($('shipping:postcode')) {
                addressPrefix = 'shipping:';
            }
            else if ($('billing_postcode')) {
                addressPrefix = 'billing_';
            }
            else {
                addressPrefix = 'billing:';
            }

            result.country = $(addressPrefix + 'country_id').value;
            result.zipcode = $(addressPrefix + 'postcode').value;
            result.city    = $(addressPrefix + 'city').value;
            result.street  = $(addressPrefix + 'street1').value;
            result.housenr = $(addressPrefix + 'street2').value;
        }
        catch (e) {
        }

        return result;
    },

    /**
     * Retrieves location and updates the map
     * @param searchValue
     * @param callback
     */
    retrieveLocation: function(searchValue, callback, errorCallback) {
        // Attach the selected shipping method
        var url  = this.config.lookupUrl;
        var params = {
            shipping_method: this.selectedShippingMethod,
        };

        if (searchValue != null) {
            params.search = searchValue;
        }
        else {
            params = Object.extend(params, this.getCurrentAddress());
        }

        this.ajaxRequest = new Ajax.Request(url, {
            parameters: params,
            method: 'get',
            onComplete: function (response) {
                try {
                    if (!response.responseJSON) {
                        return errorCallback('Empty server response');
                    }
                    if (response.responseJSON) {
                        if (!response.responseJSON.result) {
                            return errorCallback(response.responseJSON.error);
                        }
                    }

                    // Reset the markers
                    this.clearMapMarkers();

                    var currentLocation = response.responseJSON.current_location;
                    var originLatLng = currentLocation != null ? new google.maps.LatLng(currentLocation.lat, currentLocation.lng) : null;

                    // parse the location
                    var locations = response.responseJSON.locations;

                    var bounds = new google.maps.LatLngBounds();
                    var localMarkers = [];

                    for (var i = 0; i < locations.length; i++) {
                        var location = locations[i];

                        // Add the location to google maps
                        var latLng = new google.maps.LatLng(location.lat, location.lng);
                        var distance = originLatLng != null ? google.maps.geometry.spherical.computeDistanceBetween(originLatLng, latLng) : null;

                        // Create a marker
                        var marker = new google.maps.Marker({
                            position: latLng,
                            title: location.Name,

                            markerId: i,
                            locationData: location,
                            distance: distance
                        });

                        localMarkers.push(marker);
                    }

                    // Sort the markers
                    localMarkers.sort(this.sortMarkersByDistance);

                    for (i = 0; i < localMarkers.length; i++) {
                        var localMarker = localMarkers[i];

                        // Add the marker to the map
                        localMarker.setMap(this.googleMaps);

                        // Only add the 5 closest to the boundaries
                        if (i < 5) {
                            // Add the latitude and longitude to the bounds
                            bounds.extend(localMarker.position);
                        }

                        // add click listener
                        var self = this;
                        localMarker.addListener('click', function() {
                            self.setSelectedMarker(this);
                        });

                        // Store the market
                        this.markers[localMarker.markerId] = localMarker;

                        // Add the location to the list
                        this.addLocationToList(localMarker.locationData, localMarker);

                        if (!this.selectedMarker && localMarker.locationData.selected) {
                            this.selectedMarker = localMarker;
                        }
                    }

                    callback(bounds);
                    this.setSelectedMarker(this.selectedMarker);
                }
                catch (e) {
                    errorCallback(e);
                }
            }.bind(this)
        });
    },

    /**
     * Updates the map markers based on the filters
     */
    updateMapMarkers: function() {
        // Only update the markers if there are any
        if (this.markers.length == 0) {
            return;
        }

        var shouldShowOpenEarly  = $('tss-ls-show-open-early').checked;
        var shouldShowOpenLate   = $('tss-ls-show-open-late').checked;
        var shouldShowOpenSunday = $('tss-ls-show-open-sunday').checked;
        var shouldFilter = shouldShowOpenEarly || shouldShowOpenLate || shouldShowOpenSunday;

        var localMarkers = [];

        for (var i = 0; i < this.markers.length; i++) {
            var currentMarker = this.markers[i];
            if (currentMarker) {
                if (shouldFilter && !(
                    (shouldShowOpenEarly && currentMarker.locationData.is_open_early) ||
                    (shouldShowOpenLate && currentMarker.locationData.is_open_late) ||
                    (shouldShowOpenSunday && currentMarker.locationData.is_open_sunday))) {
                    currentMarker.setMap(null);
                    continue;
                }
                localMarkers.push(currentMarker);
            }
        }

        // Remove all list items
        $(this.config.locationsListDivId).update('');

        // Sort the markers
        localMarkers.sort(this.sortMarkersByDistance);

        for (var x = 0; x < localMarkers.length; x++) {
            var localMarker = localMarkers[x];

            // Add them back to the map, only if they aren't already on it
            if (localMarker.getMap() == null) {
                localMarker.setMap(this.googleMaps);
            }

            this.addLocationToList(localMarker.locationData, localMarker);
        }
    },

    /**
     * Removes all markers from the map and list
     * and resets the marker array on the shipping object
     */
    clearMapMarkers: function() {
        for (var i = 0; i < this.markers.length; i++) {
            var currentMarker = this.markers[i];
            if (currentMarker) {
                currentMarker.setMap(null);
                this.markers[i] = null;
            }
        }
        this.markers = [];

        $(this.config.locationsListDivId).update('');
        this.setSelectedMarker(null);
    },

    /**
     * Triggered when a marker is selected.
     * Enables the select button when a marker has been select.
     * @param marker
     */
    setSelectedMarker: function(marker) {
        this.selectedMarker = marker;

        // Remove all other selected markers
        $$('.tss-ls-location-item[data-marker-id].selected').each(function(element) {
            Element.removeClassName(element, 'selected');
        });

        var selectButton = $(this.config.selectButtonId);
        if (marker) {
            selectButton.addClassName('active');

            var markerId = marker.markerId;
            var markerListItem = $$('.tss-ls-location-item[data-marker-id=' + markerId + ']')[0];
            markerListItem.addClassName('selected');

            var contentString = this.getInfoWindowContent(marker.locationData);
            this.infoWindow.setContent(contentString);
            this.infoWindow.open(this.googleMaps, marker);

            // scroll list item into view
            var parent = $(this.config.locationsListDivId);
            if (parent.scrollTop > markerListItem.offsetTop - parent.offsetTop) {
                //parent.scrollTop = markerListItem.offsetTop - parent.offsetTop;
                new Effect.Tween(parent, parent.scrollTop, markerListItem.offsetTop - parent.offsetTop, { duration: 0.3 }, 'scrollTop');
            }
            else if (parent.scrollTop + parent.offsetHeight < markerListItem.offsetTop - parent.offsetTop + markerListItem.offsetHeight) {
                //parent.scrollTop = markerListItem.offsetTop - parent.offsetTop + markerListItem.offsetHeight - parent.offsetHeight;
                new Effect.Tween(parent, parent.scrollTop, markerListItem.offsetTop - parent.offsetTop + markerListItem.offsetHeight - parent.offsetHeight, { duration: 0.3 }, 'scrollTop');
            }
        }
        else {
            selectButton.removeClassName('active');
        }
    },

    /**
     * Adds the location and the marker to the list of locations
     * @param location
     * @param marker
     */
    addLocationToList: function(location, marker) {
        var locationsListDiv = $(this.config.locationsListDivId);

        // Don't add it to the list if it's already there
        if (locationsListDiv.select('div[data-marker-id="' + marker.markerId + '"]').length != 0) {
            return;
        }

        var html = '<div class="tss-ls-location-item" data-marker-id="' + marker.markerId + '">' +
            '<span class="tss-ls-name">' +
                '<span class="tss-ls-company-name">' + location.name.escapeHTML() + '</span>' +
                '<span class="tss-ls-address">' + location.street.escapeHTML() + ' ' + location.street_no.escapeHTML() + ', ' + location.city.escapeHTML() + '</span>' +
            '</span>' +
            '<span class="tss-ls-distance">(' + this.getFormattedDistanceForMarker(marker) + ')</span>' +
            '<span class="tss-ls-check"><span class="tss-ls-img"></span></span>' +
        '</div>';

        locationsListDiv.insert(html);

        locationsListDiv.select('.tss-ls-location-item[data-marker-id=' + marker.markerId + ']')[0]
            .stopObserving('click')
            .observe('click', function(event) {
                new google.maps.event.trigger(marker, 'click');

                // Prevent the event from bubbling
                event.stop();
            });
    },

    /**
     * Formats the marker to meters or kilometers
     * @param marker
     * @returns {string}
     */
    getFormattedDistanceForMarker: function(marker) {
        if (marker.distance < 1000) {
            return String(Math.round(marker.distance)).replace('.', ',') + ' m'
        }

        return String(Math.round((marker.distance/1000) * 100)/100).replace('.', ',') + ' km';
    },

    /**
     * Sorts markers by distance
     * @param a
     * @param b
     */
    sortMarkersByDistance: function(a, b) {
        if (a.distance < b.distance)
            return -1;
        if (a.distance > b.distance)
            return 1;

        return 0;
    },

    /**
     * Returns the Google Map info window content
     * @param location
     * @returns {string}
     */
    getInfoWindowContent: function(location) {
        var openingHoursHtml = '';

        if (location.has_openinghours) {
            openingHoursHtml = Translator.translate('Opening hours:') + '<br />'
            + '<table class="tss-ls-openinghours-table">';

            // Add the opening hours
            for (var x = 0; x < location.openinghours.length; x++) {
                var openingHoursItem = location.openinghours[x];

                openingHoursHtml += '<tr>' +
                    '<td style="padding-right:8px">' + openingHoursItem.day_name.escapeHTML() + '<td>' +
                    '<td>' + openingHoursItem.display.escapeHTML() + '</td>' +
                '</tr>';
            }

            openingHoursHtml += '</table>';
        }

        var contentString = '<div id="content">'+
            '<h1 id="firstHeading" class="firstHeading">' + location.name.escapeHTML() + '</h1>'+
                '<div id="bodyContent">' +
                '<p>' +
                    location.street.escapeHTML() + ' ' + location.street_no.escapeHTML() + '<br />' +
                    location.zipcode.escapeHTML() + ' ' + location.city.escapeHTML() +
                    (location.phone != null ? '<br />' + location.phone.escapeHTML() : '') +
                '</p>' +
                openingHoursHtml +
                '</div>' +
            '</div>';

        return contentString;
    },

    /**
     * Formats the location data into HTML
     * @param locationData
     * @returns {string}
     */
    formatLocationAddress: function(locationData) {
        var html  = '<strong>' + locationData.name.escapeHTML() + '</strong> <br />';
            html += locationData.street.escapeHTML() + ' ' + locationData.street_no.escapeHTML() + '<br />';
            html += locationData.zipcode.escapeHTML() + ' ' + locationData.city.escapeHTML() + '<br />';
            html += (locationData.phone != null ? locationData.phone.escapeHTML() : '');

        return html;
    },

    /**
     * Triggered when the Google maps API has been loaded.
     * Create the map as soon as the API has been loaded
     */
    googleMapsLoaded: function() {
        this.googleMaps = new google.maps.Map($(this.config.googleMapsId), {
            zoom: 8,
            center: {lat: 52.0795, lng: 5.4492},
            disableDefaultUI: true,
            zoomControl: true,
            panControl: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        this.googleGeocoder = new google.maps.Geocoder();
        this.infoWindow = new google.maps.InfoWindow();
    },

    /**
     * Called when window is resized.
     */
    onWindowResize: function () {
        try {
            var container = $('tss-pickup-container');
            var containerHeight = container.offsetHeight;
            var selector = container.select('.tss-selector')[0];
            var headerHeight = container.select('.tss-ls-header')[0].offsetHeight;
            var footerHeight = container.select('.tss-ls-footer-controls')[0].offsetHeight;
            var padding = 20;
            var contentHeight = containerHeight - headerHeight - footerHeight - (padding * 2);
            var minContentHeight = (this.isLoading || this.isClosing) ? 212 : 131;
            if (contentHeight < minContentHeight) {
                padding = 20 - (minContentHeight - contentHeight);
                if (padding < 0) {
                    padding = 0;
                }
                contentHeight = minContentHeight;
            }
            else if (contentHeight > 460) {
                contentHeight = 460;
            }
            if (this.isLoading || this.isClosing) {
                contentHeight = 212;
                headerHeight = 0;
                footerHeight = 0;
            }
            var marginTop = Math.round((containerHeight - contentHeight - headerHeight - footerHeight) / 2);
            if (marginTop < padding) {
                marginTop = padding;
            }

            selector.style.marginTop = marginTop + 'px';
            if (this.isLoading || this.isClosing) {
                return;
            }
            $('tss-ls-map').style.height = contentHeight + 'px';
            $('tss-ls-locations').style.height = (contentHeight - 68) + 'px';
            google.maps.event.trigger(this.googleMaps, 'resize');
        }
        catch (e) {}
    },

    /**
     * If a location has been selected, the location data is inserted into the input
     * and then closed off.
     */
    selectLocationAndClose: function() {
        if (this.selectedMarker != null && this.selectedMarker.locationData) {
            var locationData = this.selectedMarker.locationData;
            // TODO: btoa only supports Latin1 characters. Make this work with all UTF-8 characters.
            $('tss-ls-location-data').value = btoa(Object.toJSON(locationData));
            $(this.config.selectedLocationDivId).update(this.formatLocationAddress(locationData));
            this.close();
            this.origShippingMethod = this.selectedShippingMethod;
            this.origPickupDivHtml = $(this.config.shippingPickupContainerId).outerHTML;

            // submit LightCheckout shipping methods
            if (typeof window.Lightcheckout == 'function' && typeof window.checkout == 'object') {
                if (typeof window.checkout.submit == 'function' && typeof window.checkout.getFormData == 'function') {
                    window.checkout.submit(window.checkout.getFormData(), 'get_totals');
                }
            }
        }
    },

    /**
     * Closes off the location selector box
     */
    close: function() {
        this.isClosing = true;
        var pickupContainer = $('tss-pickup-container');
        var pickupLoader = pickupContainer.select('.tss-loader')[0];
        var pickupContent = pickupContainer.select('.tss-selector-container')[0];

        // Reset the markers
        this.clearMapMarkers();

        pickupLoader.show();
        pickupContent.hide();
        this.onWindowResize();

        // Show the pickup container
        pickupContainer.appear({
            duration: 0.3,
            from: 1,
            to: 0,
            afterFinish: function () {
                pickupContainer.hide();
            }
        });

        if (this.ajaxRequest) {
            this.ajaxRequest.transport.abort();
        }
    }
});
