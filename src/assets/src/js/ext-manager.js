"use strict";
window.ExtensionsManager = window.ExtensionsManager || {};
(function ($) {
    var $extList = $('#ext-search-list'),
        $detailsButton = $('[data-action="ext-info"]', $extList),
        installButtonSelector = '[data-action="ext-install"]',
        uninstallButtonSelector = '[data-action="ext-uninstall"]';

    $detailsButton.click(function () {
        var $this = $(this),
            packageName = $this.data('package-name'),
            $parentTr = $this.parents('tr:eq(0)');
        $('i', $this).show();
        if ($this.hasClass('loaded') && $this.hasClass('opened')) {
            $parentTr.next('.extension-info-tr').hide();
            $this.removeClass('opened');
            $('i', $this).hide();
        } else if ($this.hasClass('loaded') && !$this.hasClass('opened')) {
            $parentTr.next('.extension-info-tr').show();
            $this.addClass('opened');
            $('i', $this).hide();
        } else if (!$this.hasClass('loaded')) {
            $.ajax({
                url: window.ExtensionsManager.detailsUrl,
                data: {"packageName": packageName},
                method: "POST",
                success: function (data) {
                    if (data.content.length > 0 && false == data.error) {
                        var $details = $(data.content).filter('.details-part');
                        $parentTr.after(window.ExtensionsManager.detailsTemplate.replace('{details}', $details.html()));
                        $this.addClass('loaded opened');
                        $('i', $this).hide();
                    }
                },
                error: function (data) {
                    //console.log(data);
                }
            });
        }
        return false;
    });

    $extList.on('click', installButtonSelector, function () {
        var $this = $(this),
            packageName = $this.data('package-name');
        reportingQueueItem.executeRouteWithReportingQueueItem(
            window.ExtensionsManager.installUrl,
            {"packageName": packageName},
            'POST',
            {'endpoint':  window.ExtensionsManager.endpointUrl}
        );
        return false;
    });
    $extList.on('click', uninstallButtonSelector, function () {
        var $this = $(this),
            packageName = $this.data('package-name');
        reportingQueueItem.executeRouteWithReportingQueueItem(
            window.ExtensionsManager.uninstallUrl,
            {"packageName": packageName},
            'POST',
            {'endpoint':  window.ExtensionsManager.endpointUrl}
        );
        return false;
    });
})(jQuery);