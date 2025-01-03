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
    // var marqueeText = $.trim($('.marquee span').text());
    // if (marqueeText !== '') {
    //     $('.marquee').css('height', '1px');
    //     $('.header').css('height', '142px');
    //     $('.dark_line').css('margin', '20px -5px');
    // }
    // $("span.marquee:empty").text("ТЕСТ");


    /** Добавление/удаление товаров товаров в избранного */
    $('.favorite-svg-icon').on('click', function() {
        var favorID = $(this).attr('data-product-id');
        var doAction = '';

        if($(this).hasClass('active')) {
            doAction = 'delete';
        } else {
            doAction = 'add';
        }

        addFavorite(favorID, doAction);
    });

    function addFavorite(id, action)
    {
        var param = 'id=' + id + '&action=' + action;
        $.ajax({
            url:     '/local/ajax/add_to_favorite.php', // URL отправки запроса
            type:     'GET',
            dataType: 'html',
            data: param,
            success: function(response) { // Если Данные отправлены успешно
                var result = $.parseJSON(response);

                if(result == 1){ // Если всё чётко, то выполняем действия, которые показывают, что данные отправлены
                    $('.favorite-svg-icon[data-product-id="' + id + '"]').attr('class', 'favorite-svg-icon active');
                    var wishCount = parseInt($('.b-header-favorite .favorite-count').html()) + 1;
                    $('.b-header-favorite .favorite-count').html(wishCount); // Визуально меняем количество у иконки
                }
                if(result == 2){
                    $('.favorite-svg-icon[data-product-id="' + id + '"]').attr('class', 'favorite-svg-icon');
                    var wishCount = parseInt($('.b-header-favorite .favorite-count').html()) - 1;
                    $('.b-header-favorite .favorite-count').html(wishCount); // Визуально меняем количество у иконки
                }
            },
            error: function(jqXHR, textStatus, errorThrown){ // Ошибка
                console.log('Error: '+ errorThrown);
            }
        });
    }

    /** Загрузка заказа в Excel формате*/
    $('.download_order_excel').click(function() {
        function makeAjaxRequest() {
            return new Promise(function(resolve, reject) {
                $('#shclDefault').show();
                $('#shclDefault').shCircleLoader();
                $.ajax({
                    url: '/local/ajax/order_in_excel.php?session_id=' + BX.bitrix_sessid(),
                    success: function(data){
                        resolve(data);
                    },
                    error: function(error) {
                        reject(error);
                    }
                });
            });
        }

        makeAjaxRequest()
            .then(function(data) {
                if (data == 'true') {
                    var link = document.createElement('a');
                    link.href = '/upload/order_excel/price_order_' + BX.bitrix_sessid() + '.xlsx';
                    link.download = 'Заказ на StrProfi.xlsx';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    $.ajax({
                        url: '/local/ajax/order_in_excel.php?session_id=' + BX.bitrix_sessid() + '&delete=Y',
                        success: function(data){},
                        error: function(error) {}
                    });
                }
                $('#shclDefault').hide();
            })
            .catch(function(error) {
                $('#shclDefault').hide();
            });

        function downloadFile() {

        }

        //setTimeout(downloadFile, 10000);
    });
});