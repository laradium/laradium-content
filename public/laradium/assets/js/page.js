$(document).ready(function () {
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

    $(document).on('click', '#duplicate-page', async function (e) {
        e.preventDefault();
        $(e.target).prop('disabled', true);
        let form = $(document).find('.crud-form');

        var formData = new FormData();
        /*
         * Fix for safari FormData bug
         */
        $(form).find('input[name][type!="file"], select[name], textarea[name]').each(function (i, e) {
            if ($(e).attr('type') === 'checkbox' || $(e).attr('type') === 'radio') {
                if ($(e).is(':checked')) {
                    formData.append($(e).attr('name'), $(e).val());
                }
            } else {
                formData.append($(e).attr('name'), $(e).val());
            }
        });

        let fileInputs = $(form).find('input[name][type="file"]');

        for (let index in fileInputs) {
            if (!fileInputs.hasOwnProperty(index)) {
                continue;
            }

            let file = fileInputs[index];
            let link = $(file).closest('div').find('a');
            if (!link.length) {
                continue;
            }
            let fileUrl = link.attr('href');

            if (!fileUrl) {
                continue;
            }

            let response = await fetch(fileUrl);
            let data = await response.blob();

            let filename = fileUrl.split('/').pop();
            let fileObj = new File([data], filename, {
                type: data.type
            });

            formData.append($(file).attr('name'), fileObj);
        }

        let url = $(e.target).data('url');
        let res = await axios({
            method: 'POST',
            url: url,
            data: formData,
        });
        let {data} = await res.data;

        $(e.target).prop('disabled', false);

        if (data) {
            window.location = data.redirect_to;
        }
    });
});

