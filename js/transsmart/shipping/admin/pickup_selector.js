/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
if (!Transsmart) var Transsmart = { };
if (!Transsmart.Shipping) Transsmart.Shipping = { };

Transsmart.Shipping.PickupAdmin = Class.create(Transsmart.Shipping.Pickup, {

    /**
     * Triggered when the initialization has been done
     */
    attachListenHandler: function() {
        var self = this;

        // Attach click event to close button
        $(self.config.closeButtonId).observe('click', function(event) {
            self.close();
            event.stop();
        });

        // Wait for the document to load
        document.observe('dom:loaded', function() {
            self.detectCheckedShippingMethod();

            // Add our customer validator
            self.addValidators();

            var parentSetShippingMethod = order.setShippingMethod.bind(order);

            // Override the setShippingMethod, since we need it to determine when a shipping method was clicked
            order.setShippingMethod = function(value) {
                // Check to see if this is a pickup profile that allows a location selector
                var valueBits = value.match(/transsmartpickup_carrierprofile_([0-9]+)/);

                // Nothing to attach, it doesn't match
                if (valueBits == null || valueBits.length != 2) {
                    return parentSetShippingMethod(value);
                }

                // Not a carrierprofile that has location selector enabled
                if (self.config.carrierProfileIds.indexOf(valueBits[1]) == -1) {
                    Transsmart.Logger.log('Carrier profile with id: ' + valueBits[1] + ' does not allow location selector');
                    Transsmart.Logger.log('Allowed location selectors are: ', self.config.carrierProfileIds);
                    return parentSetShippingMethod(value);
                }

                self.selectedShippingMethod = value;
                if (self.selectedShippingMethod != self.origShippingMethod) {
                    $('tss-ls-admin-selected-location').update('');
                }
                self.attachPickupDiv($('s_method_' + value));
            };

            // We need to move the container to after the anchor-content, so it lines up correctly
            $('anchor-content').insert({
                after: $('tss-pickup-container')
            });

            $(self.config.selectButtonId).observe('click', function(event, elemnt) {
                self.selectLocationAndClose();
                event.stop();
            });

            var parentLoadAreaResponseHandler = order.loadAreaResponseHandler.bind(order);

            // Override the area response handler so that we can detect which shipping method is selected
            order.loadAreaResponseHandler = function(response) {
                parentLoadAreaResponseHandler(response);
                self.detectCheckedShippingMethod();
            }
        });
    },

    detectCheckedShippingMethod: function() {
        var checkedShippingMethod = $$('input[name="order[shipping_method]"]:checked');

        if (checkedShippingMethod.length > 0) {
            var checkedShippingMethodValue = checkedShippingMethod[0].value;
            if (/transsmartpickup_carrierprofile_[0-9]+/.test(checkedShippingMethodValue)) {
                this.selectedShippingMethod = checkedShippingMethodValue;
                this.attachPickupDiv($('s_method_' + checkedShippingMethodValue));
            }
        }
    },

    /**
     * Triggered when a location has been selected
     */
    selectLocationAndClose: function() {
        // If we have a location, we need to set the shipping method
        if (this.selectedMarker != null && this.selectedMarker.locationData) {
            var locationData = this.selectedMarker.locationData;
            $('tss-ls-location-data').value = btoa(Object.toJSON(locationData));

            // Update the div
            $('tss-ls-admin-selected-location').update(this.formatLocationAddress(locationData));

            // Close the pop-up
            this.close();
            this.origShippingMethod = this.selectedShippingMethod;
            this.origPickupDivHtml = $(this.config.shippingPickupContainerId).outerHTML;

            var data = {};
            data['order[shipping_method]'] = this.selectedShippingMethod;
            // TODO: btoa only supports Latin1 characters. Make this work with all UTF-8 characters.
            data['order[transsmart_pickup_address_data]'] = btoa(Object.toJSON(locationData));

            order.loadArea(['shipping_method', 'totals', 'billing_method'], true, data);
        }
    },

    /**
     * Adds the required validators to the validation class.
     */
    addValidators: function() {
        Validation.add(
            'validate-selected-location',
            Translator.translate("A pickup location has to be selected"),
            function(v) {
                return $('order-shipping-method-choose').visible() ? !Validation.get('IsEmpty').test(v) : true;
            });
    }

});

Transsmart.Shipping.PickupAdmin.prototype.parent = Transsmart.Shipping.Pickup.prototype;
