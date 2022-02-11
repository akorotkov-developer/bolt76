$(document).ready(function () {
    $('body').on('change', '.quantity_input', function (e) {
        var ratio = (parseFloat($(this).attr('data-ratio')) > 0) ? parseFloat($(this).attr('data-ratio')) : 1;
        var curVal = parseFloat($(this).val());

        if (isNaN(curVal)) {
            $(this).val(ratio);
        } else if(curVal < ratio) {
            $(this).val(ratio);
        } else {
            if (parseFloat((Math.trunc(curVal / ratio) * ratio).toFixed(1)) != parseFloat(Math.trunc(curVal / ratio) * ratio)) {
                $(this).val((Math.trunc(curVal / ratio) * ratio).toFixed(1));
            } else {
                $(this).val((Math.trunc(curVal / ratio) * ratio));
            }
        }
    });
});