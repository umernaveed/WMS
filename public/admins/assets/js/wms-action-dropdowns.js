(function ($) {
    'use strict';

    if (!$) return;

    function isActionHeader(text) {
        text = $.trim(String(text || '')).toLowerCase();
        return text === 'action' || text === 'actions' || text === '';
    }

    function actionColumnIndexes($table) {
        var indexes = [];
        $table.find('thead tr:first th').each(function (index) {
            if (isActionHeader($(this).text())) indexes.push(index);
        });
        return indexes;
    }

    function actionElements($cell) {
        return $cell.children('a.btn, button.btn, form, .btn-group, .dropdown')
            .filter(function () {
                return !$(this).hasClass('wms-action-dropdown') && !$(this).closest('.wms-action-dropdown').length;
            });
    }

    function wrapActionCell(cell) {
        var $cell = $(cell);
        if ($cell.data('wmsActionWrapped')) return;
        if ($cell.find('.dropdown-menu').length) return;

        var $items = actionElements($cell);
        if (!$items.length) return;

        $cell.data('wmsActionWrapped', true);
        var id = 'wms-actions-' + Math.random().toString(36).slice(2);
        var $dropdown = $('<div class="btn-group wms-action-dropdown"></div>');
        var $toggle = $('<button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions <span class="caret"></span></button>');
        var $menu = $('<ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="' + id + '"></ul>');
        $toggle.attr('id', id);

        $items.each(function () {
            var $item = $(this);
            var $li = $('<li role="presentation"></li>');

            if ($item.is('form')) {
                $item.addClass('wms-dropdown-form');
                $item.find('.btn').removeClass('btn btn-sm btn-xs btn-info btn-warning btn-danger btn-success btn-primary btn-default all_button').addClass('wms-dropdown-button');
                $li.append($item.detach());
            } else {
                $item.removeClass('btn btn-sm btn-xs btn-info btn-warning btn-danger btn-success btn-primary btn-default all_button').addClass('wms-dropdown-link');
                $li.append($item.detach());
            }

            $menu.append($li);
        });

        $dropdown.append($toggle).append($menu);
        $cell.empty().append($dropdown);
    }

    function cleanCellText(cell) {
        return $.trim($(cell).clone().find('script, style, form, button, .btn, .dropdown, .wms-action-dropdown').remove().end().text()).replace(/\s+/g, ' ');
    }

    function exportableColumnIndexes($table) {
        var indexes = [];
        $table.find('thead tr:first th').each(function (index) {
            if (!isActionHeader($(this).text())) indexes.push(index);
        });
        return indexes;
    }

    function tableRows($table, indexes) {
        var rows = [];
        var header = [];
        $table.find('thead tr:first th').each(function (index) {
            if (indexes.indexOf(index) !== -1) header.push(cleanCellText(this));
        });
        if (header.length) rows.push(header);

        $table.find('tbody tr:visible').each(function () {
            var row = [];
            var $cells = $(this).children('td');
            indexes.forEach(function (index) {
                row.push(cleanCellText($cells.get(index)));
            });
            if (row.join('').trim() !== '') rows.push(row);
        });

        return rows;
    }

    function csvValue(value) {
        value = String(value == null ? '' : value);
        return '"' + value.replace(/"/g, '""') + '"';
    }

    function downloadFile(filename, mime, content) {
        var blob = new Blob([content], { type: mime });
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(function () { URL.revokeObjectURL(url); }, 250);
    }

    function exportFilename($table, extension) {
        var title = $.trim($('.page-title, .card-title, h1, h2, h3').first().text()) || document.title || 'wms-export';
        title = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') || 'wms-export';
        return title + '-' + new Date().toISOString().slice(0, 10) + '.' + extension;
    }

    function exportCsv($table) {
        var indexes = exportableColumnIndexes($table);
        var rows = tableRows($table, indexes);
        var csv = rows.map(function (row) { return row.map(csvValue).join(','); }).join('\r\n');
        downloadFile(exportFilename($table, 'csv'), 'text/csv;charset=utf-8', '\ufeff' + csv);
    }

    function exportExcel($table) {
        var indexes = exportableColumnIndexes($table);
        var rows = tableRows($table, indexes);
        var html = '<html><head><meta charset="utf-8"></head><body><table border="1">';
        rows.forEach(function (row, rowIndex) {
            html += '<tr>' + row.map(function (cell) {
                var tag = rowIndex === 0 ? 'th' : 'td';
                return '<' + tag + '>' + $('<div>').text(cell).html() + '</' + tag + '>';
            }).join('') + '</tr>';
        });
        html += '</table></body></html>';
        downloadFile(exportFilename($table, 'xls'), 'application/vnd.ms-excel;charset=utf-8', html);
    }

    function printTable($table) {
        var indexes = exportableColumnIndexes($table);
        var rows = tableRows($table, indexes);
        var title = $('<div>').text(document.title || 'WMS Export').html();
        var html = '<!doctype html><html><head><meta charset="utf-8"><title>' + title + '</title>';
        html += '<style>body{font-family:Arial,sans-serif;padding:24px;color:#111827}h1{font-size:20px;margin:0 0 16px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #d1d5db;padding:8px;text-align:left;font-size:12px}th{background:#f3f4f6}</style>';
        html += '</head><body><h1>' + title + '</h1><table>';
        rows.forEach(function (row, rowIndex) {
            html += '<tr>' + row.map(function (cell) {
                var tag = rowIndex === 0 ? 'th' : 'td';
                return '<' + tag + '>' + $('<div>').text(cell).html() + '</' + tag + '>';
            }).join('') + '</tr>';
        });
        html += '</table></body></html>';

        var printWindow = window.open('', '_blank');
        if (!printWindow) return;
        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }

    function attachExportToolbar(table) {
        var $table = $(table);
        if ($table.data('wmsExportAttached')) return;
        if (!$table.find('thead th').length || !$table.find('tbody').length) return;

        var indexes = exportableColumnIndexes($table);
        if (!indexes.length) return;

        $table.data('wmsExportAttached', true);
        var $toolbar = $('<div class="wms-table-export-toolbar"><span class="wms-table-export-title">Export</span><button type="button" class="btn btn-sm btn-default" data-wms-export="csv"><i class="fa fa-download"></i> CSV</button><button type="button" class="btn btn-sm btn-default" data-wms-export="excel"><i class="fa fa-file-excel-o"></i> Excel</button><button type="button" class="btn btn-sm btn-default" data-wms-export="print"><i class="fa fa-print"></i> Print</button></div>');
        var $wrapper = $table.closest('.dataTables_wrapper');
        if ($wrapper.length) {
            $wrapper.prepend($toolbar);
        } else {
            $table.before($toolbar);
        }

        $toolbar.on('click', '[data-wms-export]', function () {
            var type = $(this).data('wmsExport');
            if (type === 'csv') exportCsv($table);
            if (type === 'excel') exportExcel($table);
            if (type === 'print') printTable($table);
        });
    }

    function normalizeTableActions(context) {
        $(context || document).find('table').each(function () {
            var $table = $(this);
            attachExportToolbar(this);
            var indexes = actionColumnIndexes($table);
            if (!indexes.length) return;

            $table.find('tbody tr').each(function () {
                var $cells = $(this).children('td');
                indexes.forEach(function (index) {
                    var cell = $cells.get(index);
                    if (cell) wrapActionCell(cell);
                });
            });
        });
    }

    $(function () {
        normalizeTableActions(document);
        $(document).on('draw.dt', function (event) {
            normalizeTableActions($(event.target).closest('.dataTables_wrapper').parent());
        });
        setTimeout(function () { normalizeTableActions(document); }, 600);
    });
})(window.jQuery);
