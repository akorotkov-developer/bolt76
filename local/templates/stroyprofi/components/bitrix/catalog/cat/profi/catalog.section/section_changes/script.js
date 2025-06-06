$( document ).ready(function() {
    /* View Mode */
    // Product List
    $('#list-view').on('click',function() {
        $('#list-view').addClass('active');
        $('#grid-view').removeClass('active');
        $('#table-view').removeClass('active');

        $('.page_content .product-layout').attr('class', 'product-layout product-list col-xs-12');
        localStorage.setItem('display', 'list');

        // Сделать блоки одной высоты
        $('.product-layout.product-list').each(function(){
            $(this).removeAttr('style');;
            $(this).children().removeAttr('style');;
        });

        $('.b-grid-list-view').fadeIn();
        $('.b-table-view').fadeOut();
    });
    // Product Grid
    $('#grid-view').on('click',function() {
        $('#grid-view').addClass('active');
        $('#list-view').removeClass('active');
        $('#table-view').removeClass('active');

        // What a shame bootstrap does not take into account dynamically loaded columns
        cols = $('#column-right, #column-left').length;
        if (cols == 2) {
            $('.page_content .product-layout').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-6 col-xs-12');
        } else if (cols == 1) {
            $('.page_content .product-layout').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-4 col-xs-12');
        } else {
            $('.page_content .product-layout').attr('class', 'product-layout product-grid col-lg-3 col-md-4 col-sm-4 col-xs-12');
        }
        localStorage.setItem('display', 'grid');

        // Сделать блоки одной высоты
        setTimeout(setColumnHeight, 1000);

        $('.b-grid-list-view').fadeIn();
        $('.b-table-view').fadeOut();
    });
    // Table View
    $('#table-view').on('click',function() {
        $('#grid-view').removeClass('active');
        $('#list-view').removeClass('active');
        $('#table-view').addClass('active');

        $('.b-grid-list-view').fadeOut();
        $('.b-table-view').fadeIn();

        localStorage.setItem('display', 'table');
    });

    function setColumnHeight() {
        let column = 0;
        $('.product-layout.product-grid').each(function(){
            $(this).each(function(){
                h = $(this).height();
                if (h > column) {
                    column = h;
                }
            });
        });
        $('.product-layout.product-grid').each(function(){
            $(this).height(column);
            $(this).children().height(column);
        });
    }

    if (localStorage.getItem('display') == 'list') {
        $('#list-view').trigger('click');
    } else if (localStorage.getItem('display') == 'table') {
        $('#table-view').trigger('click');
    } else {
        $('#grid-view').trigger('click');
    }

    /**
     * Добавление товара в корзину
     */
    $('.btn.btn-cart').live('click', function (e) {
        e.preventDefault();
        el = $(this);
        var inputval = $(el).closest('.b-bottom-addcart').find('.quantity_input');
        var inputData = $(el).attr('data-ratio');

        if (inputval.val() == '') {
            var value = 0;
            // TODO попарвить этот момент
            inputData = 'NaN';
            if (inputData == 'NaN') {
                value = 1;
            } else {
                value = inputData;
            }
            inputval.val(value);
        }

        $.post("/cart/add_to_cart.php", inputval.serialize(), function (data) {
            if ($.trim(data) != '') {
                $('.cart_info_holder').html(data);
                inputval.val('');
                recountForm();
                if ($(el).closest(".fancybox-overlay").length > 0) {
                    $(".fancybox-close").click();
                }
                $("#notification").css("top", "0");
                $("#notification").animate({"top": "0"}, 400).delay(2000).animate({"top": "-100px"}, 400);

                window.basketController.setLinks();
            }
        })
    });

    // Обработчики событий для кнопок плюс и минус
    /** Уменьшение количества*/
    $('.s-span-minus-button').bind('click', function(event) {
        var quantInput = $(this).siblings('.input_holder').find('.quantity_input');
        var dataRatio = $(this).siblings('.input_holder').find('.quantity_input').attr('data-ratio');
        var ratio = 1;
        var val = 0;

        if (dataRatio != 'NaN') {
            ratio = Decimal(dataRatio);
        }

        if (quantInput.val() == '') {
            val = Decimal(0);
        } else {
            val = Decimal(quantInput.val());
        }

        if (val > 0) {
            quantInput.val(val.minus(ratio));
        }
    });

    /** Увеличение количеств*/
    $('.s-span-plus-button').bind('click', function(event) {
        var quantInput = $(this).siblings('.input_holder').find('.quantity_input');
        var dataRatio = $(this).siblings('.input_holder').find('.quantity_input').attr('data-ratio');
        var ratio = 1;
        var val = 0;

        if (dataRatio != 'NaN') {
            ratio = parseFloat(dataRatio);
        }

        if (quantInput.val() == '') {
            val = 0;
        } else {
            val = parseFloat(quantInput.val());
        }

        quantInput.val(val + ratio);
    });

    /** Добавление товара в корзину для кнопки в табличном виде */
    $('.btn-list-add-to-cart').bind("click", function(e) {
        e.preventDefault();
        var el = $(this);
        var inputval = $(el).parent().siblings('.buy').find('input');
        var inputData = $(el).closest('tr').find('.buy .input_holder input').attr('data-ratio');

        if (inputval.val() == '') {
            var value = 0;
            if (inputData == 'NaN') {
                value = 1;
            } else {
                value = inputData;
            }
            inputval.val(value);
        }

        $.post("/cart/add_to_cart.php", $(el).closest('tr').find('.buy .input_holder input').serialize(), function (data) {
            if ($.trim(data) != '') {
                $('.cart_info_holder').html(data);
                $(el).closest('tr').find('.buy .input_holder input').val('');
                recountForm();
                if ($(el).closest(".fancybox-overlay").length > 0) {
                    $(".fancybox-close").click();
                }
                $("#notification").css("top", "0");
                $("#notification").animate({"top": "0"}, 400).delay(2000).animate({"top": "-100px"}, 400);

                window.basketController.setLinks();
            }
        });
    });

    /** Добавление товара к сравнению */
    $('.compare-svg-icon-element-list').bind("click", function(e) {
        if ($(this).parent().hasClass('active')) {
            deleteCompare($(this).attr('data-product-id'));

        } else {
            addCompare($(this).attr('data-product-id'));
        }
    });

    function deleteCompare(productId) {
        let currentUrl = window.location.href;
        let separator = currentUrl.includes('?') ? '&' : '?';
        let compareLink = currentUrl + separator + 'action=DELETE_FROM_COMPARE_LIST&id=' + productId + '&ajax_action=Y';

        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: compareLink,
            onsuccess: function(response) {

            },
            onfailure: function(error) {

            }
        });
    }

    function addCompare(productId) {
        let currentUrl = window.location.href;
        let separator = currentUrl.includes('?') ? '&' : '?';
        let compareLink = currentUrl + separator + 'action=ADD_TO_COMPARE_LIST&id=' + productId + '&ajax_action=Y';

        // Отправляем AJAX-запрос
        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: compareLink,
            onsuccess: function(response) {
                // Проверяем статус ответа
                if (response.STATUS === 'OK') {


                    /*// Создаем модальное окно
                    const popup = BX.PopupWindowManager.create('CatalogElementBasket_' + productId, null, {
                        autoHide: false,
                        offsetLeft: 0,
                        offsetTop: 0,
                        overlay: true,
                        closeByEsc: true,
                        titleBar: true,
                        closeIcon: true,
                        contentColor: 'white',
                        className: 'bx-block-confirm'
                    });

                    // Устанавливаем заголовок окна
                    popup.setTitleBar('Сравнение товаров');

                    // Создаем контент для модального окна
                    const content = `
                    <div style="padding: 20px; text-align: center;">
                        <p>Товар добавлен в список сравнения</p>
                        <button class="btn btn-default btn-buy btn-sm" onclick="window.location.href='/catalog/compare/';" style="margin-top: 10px; padding: 10px 20px; cursor: pointer;">
                            Перейти в список сравнения
                        </button>
                    </div>
                `;*/

                    /*// Устанавливаем контент в модальное окно
                    popup.setContent(content);

                    // Открываем модальное окно
                    popup.show();*/
                }
            },
            onfailure: function(error) {

            }
        });
    }

});
