$(document).ready(function () {
    $('body').on('change', '.quantity_input', function (e) {
        var ratio = ($(this).attr('data-ratio') != 'NaN') ? parseFloat($(this).attr('data-ratio')) : 'NaN';
        var curVal = parseFloat($(this).val());

        if (ratio != 'NaN') {
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

            var obToolTip = $(this).parent().siblings('.kratnostHelperHolder');
            obToolTip.fadeIn('200');
            setTimeout(function() { obToolTip.fadeOut('200'); }, 2000);
        }
    });

    // Функционал отслеживает бездействие пользователя в течении 3 минут, если ничего не произошло редиректит его на авторизацию киоска
 /*   if (isKioskBuyer) {
        var mytime = mytime1 = 180;
        document.onmousemove = document.onkeydown = document.onscroll = document.ontouchstart = function() {mytime = mytime1};
        setInterval(function(){
            console.log(mytime);
            mytime --;
            if (mytime <=0 ) {
                location.href = "/kiosk_auth/";
            }
        }, 1000);
    }*/

    /**
     * Проверка бегущей строки если, в ней пусто, то нужно скрыть её и поменять стиль
     */
    var marqueeText = $.trim($('.marquee span').text());
    if (marqueeText == '') {
        $('.marquee').css('height', '1px');
        $('.header').css('height', '142px');
    }
});