"use strict";
window.ExtensionsManager = window.ExtensionsManager || {};
(function ($) {
    var $button = $('[data-action="ext-info"]');
    $button.click(function () {
        var $this = $(this),
            packageName = $this.data('package-name');
        $.ajax({
            url: window.ExtensionsManager.detailsUrl,
            data: {"packageName" : packageName},
            method: "POST",
            success : function (data) {
                if (data.content.length > 0 && false == data.error) {
                    var $content = $(data.content);
                    console.log($content);
                }
            },
            error: function (data) {
                //console.log(data);
            }
        });
        return false;
    });
})(jQuery);