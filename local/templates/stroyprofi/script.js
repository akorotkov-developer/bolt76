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

    // Автоматическое появление бегущей строки при наличии в ней текста
    $(document).ready(function() {
        var marqueeContent = $('.marquee').text().trim();
        if (marqueeContent !== '') { // Если есть текст
            $('.dark_line').css('margin', '0px -5px'); // Меняем margin
            $('.marquee').css('display', 'block');
        } else {
            $('.marquee').css('display', 'none'); // Скрываем .marquee
        }
    });


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

    // Обработчик клика на SVG
    $('.compare-svg-icon-element-detail').on('click', function() {
        // Находим связанный чекбокс
        let checkbox = $(this).closest('label').find('.compare-checkbox');

        checkbox.trigger('click');
        checkbox.prop('checked', !checkbox.prop('checked'));

        // Добавляем/удаляем класс active у родительского блока
        let parentBlock = $(this).closest('.b-compare');
        if (parentBlock.hasClass('active')) {
            let compareCountElement = $('.b-header-compare .compare-count'); // Находим элемент с числом
            let currentCount = parseInt(compareCountElement.text(), 10); // Получаем текущее число
            compareCountElement.text(currentCount - 1); // Уменьшаем на 1 и обновляем текст
        } else {
            let compareCountElement = $('.b-header-compare .compare-count'); // Находим элемент с числом
            let currentCount = parseInt(compareCountElement.text(), 10); // Получаем текущее число
            compareCountElement.text(currentCount + 1); // Увеличиваем на 1 и обновляем текст
        }
        parentBlock.toggleClass('active');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const filterToggleBtn = document.querySelector('#btn-filters-action');
    const closeFilterBtn = document.querySelector('.close-filter-btn');
    const productFilter = document.querySelector('.b-product-filter');
    const filterOverlay = document.querySelector('.filter-overlay');

    // Открытие фильтра
    if (filterToggleBtn) {
        filterToggleBtn.addEventListener('click', function() {
            productFilter.classList.add('active');
            filterOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Закрытие фильтра (по кнопке)
    if (closeFilterBtn) {
        closeFilterBtn.addEventListener('click', closeFilter);
    }

    // Закрытие фильтра (по оверлею)
    if (filterOverlay) {
        filterOverlay.addEventListener('click', closeFilter);
    }

    // Функция закрытия
    function closeFilter() {
        productFilter.classList.remove('active');
        filterOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Закрытие при нажатии Esc
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeFilter();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const filterToggleBtn = document.querySelector('#btn-filters-action');
    const closeFilterBtn = document.querySelector('.close-filter-btn');
    const productFilter = document.querySelector('.b-product-filter');
    const filterOverlay = document.querySelector('.filter-overlay');

    // Открытие фильтра
    if (filterToggleBtn) {
        filterToggleBtn.addEventListener('click', function() {
            productFilter.classList.add('active');
            filterOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Закрытие фильтра (по кнопке)
    if (closeFilterBtn) {
        closeFilterBtn.addEventListener('click', closeFilter);
    }

    // Закрытие фильтра (по оверлею)
    if (filterOverlay) {
        filterOverlay.addEventListener('click', closeFilter);
    }

    // Функция закрытия
    function closeFilter() {
        productFilter.classList.remove('active');
        filterOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Закрытие при нажатии Esc
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeFilter();
        }
    });
});

// Куки

window.onload = function() {
    if (!localStorage.getItem('cookieConsent')) {
        document.getElementById('cookieBanner').style.display = 'block';
    }
    if (localStorage.getItem('analyticsCookies') === 'true') {
        loadAnalytics();
    }
};

function acceptCookies() {
    localStorage.setItem('cookieConsent', 'true');
    localStorage.setItem('analyticsCookies', 'true');
    document.getElementById('cookieBanner').style.display = 'none';
    loadAnalytics();
}

function showCookieSettings() {
    document.getElementById('cookieSettings').style.display = 'flex';
}

function saveCookieSettings() {
    const analyticsCookies = document.getElementById('analyticsCookies').checked;
    localStorage.setItem('cookieConsent', 'true');
    localStorage.setItem('analyticsCookies', analyticsCookies);
    document.getElementById('cookieSettings').style.display = 'none';
    document.getElementById('cookieBanner').style.display = 'none';
    if (analyticsCookies) {
        loadAnalytics();
    }
}

function closeCookieSettings() {
    document.getElementById('cookieSettings').style.display = 'none';
}
