/**
 * @category    Transsmart
 * @package     Transsmart_Shipping
 * @copyright   Copyright (c) 2016 Techtwo Webdevelopment B.V. (http://www.techtwo.nl)
 */
if (!Transsmart) var Transsmart = { };

/**
 * Log helper to make logging easier.
 * Regardless of whether console logging is enabled or not.
 * @type {{log: Function}}
 */
Transsmart.Logger = {
    log: function() {
        if (typeof console != 'undefined' && typeof console.log == 'function') {
            return console.log.apply(console, arguments);
        }
    }
};