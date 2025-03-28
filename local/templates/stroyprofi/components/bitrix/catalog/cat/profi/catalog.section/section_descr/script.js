$( document ).ready(function() {
    // Добавлять товары в корзину по нажатию на enter
    $('.quantity_input').bind("keypress", function(e) {
        if (e.keyCode == 13) {
            $(this).parent().parent().parent().parent().siblings('.cart_td').find('.add_to_cart_one').trigger('click');

            e.preventDefault();
            return false;
        }
    });

    /** Уменьшение количества*/
    $('.s-span-minus-button').bind('click', function(event) {
        var quantInput = $(this).siblings('.input_holder').find('.quantity_input');
        var dataRatio = $(this).siblings('.input_holder').find('.quantity_input').attr('data-ratio');
        var ratio = 1;
        var val = 0;

        if (dataRatio != 'NaN') {
            ratio = new Decimal(dataRatio);
        }

        if (quantInput.val() == '') {
            val = new Decimal(0);
        } else {
            val = new Decimal(quantInput.val());
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
            ratio = new Decimal(dataRatio);
        }

        if (quantInput.val() == '') {
            val = new Decimal(0);
        } else {
            val = new Decimal(quantInput.val());
        }

        quantInput.val(val.plus(ratio));
    });

    /** Добавление товара в корзину */
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
});

$(document).ready(function() {
    $(window).scroll(function() {
        var tdOffset = $('.element_product_tr .section_description').offset().top;
        var tdHeight = $('.element_product_tr .section_description').height();
        var scrollTop = $(window).scrollTop();
        var image = $('.section_description a img');
        var windowHeight = $(window).height();

        var isBottom = scrollTop > tdOffset + tdHeight - windowHeight;

        if (scrollTop > tdOffset/* && !isBottom*/) {
            image.css({'position': 'fixed', 'top': '0'});
        } /*else if (isBottom) {
            image.css({'position': 'absolute', 'bottom': '0', 'top': 'auto'});
        }*/ else {
            image.css({'position': 'absolute', 'top': '0px'});
        }
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

                }
            },
            onfailure: function(error) {

            }
        });
    }
});