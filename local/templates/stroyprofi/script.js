$(document).ready(function () {
    $('body').on('change', '.quantity_input', function (e) {
        var ratio = ($(this).attr('data-ratio') != 'NaN') ? parseFloat($(this).attr('data-ratio')) : 'NaN';
        var curVal = parseFloat($(this).val());

        console.log('ratio');
        console.log(ratio);

        if (ratio != 'NaN') {
            console.log('Попали сюда');

            if (isNaN(curVal)) {
                $(this).val(ratio);
            } else if (curVal < ratio) {
                $(this).val(ratio);
            } else {
                if (parseFloat((Math.trunc(curVal / ratio) * ratio).toFixed(1)) != parseFloat(Math.trunc(curVal / ratio) * ratio)) {
                    $(this).val((Math.trunc(curVal / ratio) * ratio).toFixed(1));
                } else {
                    $(this).val((Math.trunc(curVal / ratio) * ratio));
                }
            }
        } else {
            console.log('Должно ничего не происходить!');
        }
    });
});