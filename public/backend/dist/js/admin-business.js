(function (window, $) {
    'use strict';

    window.initProcessingDataTable = function (selector, options) {
        var $table = $(selector);

        if (!$table.length || !$.fn.DataTable) {
            return null;
        }

        var settings = $.extend(true, {
            processing: false,
            responsive: true,
            autoWidth: false,
            language: {
                processing: '<span class="app-dots" aria-hidden="true"><span></span><span></span><span></span><span></span></span><span class="app-sr">Processing, please wait...</span>'
            }
        }, options || {});

        var usesRemoteData = Boolean(settings.ajax || settings.serverSide);

        if (!usesRemoteData) {
            settings.processing = false;
        }

        var table = $table.DataTable(settings);
        var hideTimer = null;
        var $container = $(table.table().container());

        function processingElement() {
            return $container.find('div.dt-processing, div.dataTables_processing');
        }

        function removeProcessing() {
            clearTimeout(hideTimer);
            processingElement().remove();
        }

        function showProcessing() {
            clearTimeout(hideTimer);
            processingElement().stop(true, true).show();
        }

        function hideProcessing() {
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                processingElement().stop(true, true).hide();
            }, 80);
        }

        if (!usesRemoteData) {
            removeProcessing();
            table.on('draw.dt search.dt length.dt page.dt order.dt', removeProcessing);

            return table;
        }

        hideProcessing();
        table.on('processing.dt', function (event, settings, processing) {
            processing ? showProcessing() : hideProcessing();
        });

        return table;
    };
})(window, window.jQuery);
