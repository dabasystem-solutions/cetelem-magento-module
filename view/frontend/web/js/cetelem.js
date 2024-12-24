define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    function main(config, element) {
        var $element = $(element);
        var url = config.AjaxUrl;
        var centro = config.CodCentro;

        var amount = $('#opt-price').text();
        $(document).ready(function () {
            console.log($('#opt-price').text());
            setTimeout(function(){
                checkData(amount);
            },2000);
        });
        $('#opt-price').bind('DOMSubtreeModified', function () {
            checkData(amount);
            console.log($('#opt-price').text());
        });

        function checkData(amount) {
            $.ajax({
                context: '#eCalculadoraCetelemDiv',
                url: url,
                type: "get",
                data: {cantidad: amount, codCentro: centro},
            }).done(function (data) {
                $('#eCalculadoraCetelemDiv').html(data.output);
                return true;
            });
        }
    }
})
