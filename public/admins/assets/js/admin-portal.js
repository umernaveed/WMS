(function ($) {
    'use strict';

    function renderSearchResults($box, results) {
        if (!results.length) {
            $box.html('<div class="portal-search-result"><strong>No matches found</strong><small>Try a tracking number, mailbox, invoice number, or customer name.</small></div>').show();
            return;
        }

        var html = results.map(function (item) {
            return '<a class="portal-search-result" href="' + item.url + '">' +
                '<strong>' + escapeHtml(item.title) + '</strong>' +
                '<small>' + escapeHtml(item.type) + (item.meta ? ' - ' + escapeHtml(item.meta) : '') + '</small>' +
                '</a>';
        }).join('');

        $box.html(html).show();
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function normalizeUrl(url) {
        return String(url || '').replace(/#.*$/, '').replace(/\/$/, '');
    }

    function openPortalMenu($submenu, animate) {
        var $toggle = $('.portal-toggle[data-target="#' + $submenu.attr('id') + '"]');
        $toggle.removeClass('collapsed');

        if (animate) {
            $submenu.stop(true, true).slideDown(140);
        } else {
            $submenu.show();
        }
    }

    $(function () {
        var storageKey = 'wms-admin-open-sidebar-menu';
        var currentUrl = normalizeUrl(window.location.href);
        var openedFromCurrentUrl = false;

        $('.portal-submenu a').each(function () {
            var linkUrl = normalizeUrl(this.href);

            if (linkUrl && currentUrl === linkUrl) {
                $(this).addClass('is-active');
                openPortalMenu($(this).closest('.portal-submenu'), false);
                openedFromCurrentUrl = true;
            }
        });

        if (!openedFromCurrentUrl) {
            var storedMenu = window.localStorage ? localStorage.getItem(storageKey) : null;
            if (storedMenu && $(storedMenu).hasClass('portal-submenu')) {
                openPortalMenu($(storedMenu), false);
            }
        }

        $('.portal-toggle').on('click', function () {
            var target = $(this).data('target');
            var $target = $(target);
            $(this).toggleClass('collapsed');
            $target.slideToggle(140);

            if (window.localStorage) {
                if ($(this).hasClass('collapsed')) {
                    localStorage.removeItem(storageKey);
                } else {
                    localStorage.setItem(storageKey, target);
                }
            }
        });

        $('.portal-submenu a').on('click', function () {
            var menuId = $(this).closest('.portal-submenu').attr('id');

            if (window.localStorage && menuId) {
                localStorage.setItem(storageKey, '#' + menuId);
            }
        });

        var timer = null;
        var $input = $('#portal-global-search');
        var $results = $('#portal-search-results');

        $input.on('input', function () {
            var query = $.trim($(this).val());
            clearTimeout(timer);

            if (query.length < 2) {
                $results.hide().empty();
                return;
            }

            timer = setTimeout(function () {
                $.get(APP_URL + '/admin/global-search', { q: query })
                    .done(function (response) {
                        renderSearchResults($results, response.results || []);
                    })
                    .fail(function () {
                        $results.html('<div class="portal-search-result"><strong>Search unavailable</strong><small>Please refresh and try again.</small></div>').show();
                    });
            }, 220);
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.portal-search').length) {
                $results.hide();
            }
        });
    });

    window.openBulkUploadModal = function () {
        $('#bulk-modal').addClass('is-open');
    };

    window.closeBulkUploadModal = function () {
        $('#bulk-modal').removeClass('is-open');
    };
})(jQuery);
