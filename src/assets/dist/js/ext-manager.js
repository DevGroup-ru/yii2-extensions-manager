"use strict";
window.ExtensionsManager = window.ExtensionsManager || {};
(function ($) {
    var $root = $('html, body'),
        $extList = $('#ext-search-list, #extensions-list'),
        $detailsButton = $('[data-action="ext-info"]', $extList),
        runDeferredTaskButtonSelector = '[data-action="run-ext-task"]';

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

    $extList.on('click', runDeferredTaskButtonSelector, function () {
        var $this = $(this),
            packageName = $this.data('package-name'),
            taskType = $this.data('ext-task');
        reportingQueueItem.executeRouteWithReportingQueueItem(
            window.ExtensionsManager.runTaskUrl,
            {
                "packageName": packageName,
                "taskType" : taskType
            },
            'POST',
            {
                'endpoint':  window.ExtensionsManager.endpointUrl,
                'afterCallback' : function (element, status) {
                    if (4 == status) {
                        var button = '<div class="modal-footer"><button data-action="ext-done-refresh" class="btn btn-success pull-right">'
                            + window.ExtensionsManager.buttonText
                            + '</button></div>';
                        element.parents('.modal-body').after(button);
                    }
                }
            }
        );
        return false;
    });

    $root.on('click', '[data-action="ext-done-refresh"]', function() {
        window.location = window.location.href;
        return false;
    });

})(jQuery);