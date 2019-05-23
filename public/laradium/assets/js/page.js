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
        if (alert.length && alert.is(':visible')) {
            gap = 95;
        }

        if (y >= gap) {
            elem.css('margin-top', y - gap);
        } else {
            elem.css('margin-top', 0)
        }
    });

    $(document).on('click', '#duplicate-page', (e) => {
        e.preventDefault();
        $(e.target).prop('disabled', true);
        let form = $(document).find('.crud-form');

        var form_data = new FormData();
        /*
         * Fix for safari FormData bug
         */
        $(form).find('input[name][type!="file"], select[name], textarea[name]').each(function (i, e) {
            if ($(e).attr('type') === 'checkbox' || $(e).attr('type') === 'radio') {
                if ($(e).is(':checked')) {
                    form_data.append($(e).attr('name'), $(e).val());
                }
            } else {
                form_data.append($(e).attr('name'), $(e).val());
            }
        });

        $(form).find('input[name][type="file"]').each(async function (i, e) {
            let fileUrl = $(e).closest('div').find('a').attr('href');
            if (fileUrl) {
                let response = await fetch(fileUrl);
                let data = await response.blob();
                let metadata = {
                    type: 'image/jpeg'
                };
                let filename = fileUrl.split('/').pop();
                let file = new File([data], filename, metadata);
                setTimeout(function () {
                    form_data.append($(e).attr('name'), file);
                }, 800);
            }
        });


        let url = $(e.target).data('url');

        setTimeout(function () {
            axios({
                method: 'POST',
                url: url,
                data: form_data,
            }).then(res => {
                $(e.target).prop('disabled', false);

                window.location = res.data.data.redirect_to;
            }).catch(e => {
                $(e.target).prop('disabled', false);
            })
        }, 1000);
    });
});

