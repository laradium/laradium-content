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
        var elem = jQuery('#page-sidebar');
        var y = $(document).scrollTop();

        if (y >= 35) {
            elem.css('margin-top', y - 35);
        } else {
            elem.css('margin-top', 0)
        }
    });
});

