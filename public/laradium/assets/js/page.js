$(document).ready(function () {
    function setPreviewLink() {
        var previewPage = $('#preview-page');

        if (!previewPage.length) {
            return false;
        }

        var links = previewPage.data('links');
        var locale = $('.language-select').val();

        var url = 'javascript:;';
        links.forEach(function (link) {
            if (link.iso_code === locale) {
                url = link.url;
            }
        });

        previewPage.attr('href', url);

        if (url === 'javascript:;') {
            previewPage.addClass('disabled');
        } else {
            previewPage.removeClass('disabled')
        }
    }

    setPreviewLink();

    $(document).on('change', '.language-select', function () {
        setPreviewLink();
    });

    $(document).on('scroll', function () {
        var elem = $('#page-sidebar');
        var y = $(document).scrollTop();
        var alert = $('.alert');

        var gap = 35;
        if (alert.length && alert.is(":visible")) {
            gap = 95;
        }

        if (y >= gap) {
            elem.css('margin-top', y - gap);
        } else {
            elem.css('margin-top', 0)
        }
    });
});

