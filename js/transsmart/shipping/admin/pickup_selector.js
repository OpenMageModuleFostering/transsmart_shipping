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
            // Add our custom validator
            self.addValidators();

            var parentSetShippingMethod = order.setShippingMethod.bind(order);

            // Override the setShippingMethod, since we need it to determine when a shipping method was clicked
            order.setShippingMethod = function(value) {
                self.selectedShippingMethod = value;
                self.selectedCarrierProfile = null;

                // Does this shipping method have the location selector enabled?
                if (typeof self.config.shippingMethods[self.selectedShippingMethod] == 'undefined') {
                    Transsmart.Logger.log('Shipping method ' + self.selectedShippingMethod + ' does not allow location selector');
                    Transsmart.Logger.log('Allowed location selectors are: ', self.config.shippingMethods);
                    return parentSetShippingMethod(value);
                }

                self.selectedCarrierProfile = self.config.shippingMethods[this.selectedShippingMethod];

                if (self.selectedShippingMethod != self.origShippingMethod) {
                    $('tss-ls-admin-selected-location').update('');
                }
                self.attachPickupDiv($('s_method_' + value));

                // save the selected shipping method without reloading the shipping methods block
                var data = {};
                data['order[shipping_method]'] = self.selectedShippingMethod;
                order.loadArea(['totals', 'billing_method'], true, data);
            };

            // We need to move the container to after the anchor-content, so it lines up correctly
            $('anchor-content').insert({
                after: $('tss-pickup-container')
            });

            $(self.config.selectButtonId).observe('click', function(event, elemnt) {
                self.selectLocationAndClose();
                event.stop();
            });
        });
    },

    updateShippingMethods: function () {
        // Check which shipping method has been selected
        var checkedShippingMethods = $$('input[name="order[shipping_method]"]:checked');
        if (checkedShippingMethods.length != 0) {
            this.attachPickupDiv(checkedShippingMethods[0]);
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
            }
        );
    }
});

Transsmart.Shipping.PickupAdmin.prototype.parent = Transsmart.Shipping.Pickup.prototype;
