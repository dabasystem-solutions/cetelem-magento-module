define([
    'jquery'
], function ($) {
    'use strict';

    var mixin = {

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            this._super($this, $widget);

            var iprice = $widget.options.jsonConfig.sprice[this.getProduct()];
            $('#opt-price').html(iprice);
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            this._super($this, $widget);

            var iprice = $widget.options.jsonConfig.sprice[this.getProduct()];
            $('#opt-price').html(iprice);
        }
    };

    return function (targetWidget) {
        $.widget('mage.SwatchRenderer', targetWidget, mixin);
        return $.mage.SwatchRenderer;
    };
});