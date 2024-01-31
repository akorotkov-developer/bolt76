$( document ).ready(function() {
    /* View Mode */
    // Product List
    $('#list-view').on('click',function() {
        $('#list-view').addClass('active');
        $('#grid-view').removeClass('active');
        $('.page_content .product-layout').attr('class', 'product-layout product-list col-xs-12');
        localStorage.setItem('display', 'list');

        // Сделать блоки одной высоты
        $('.product-layout.product-list').each(function(){
            $(this).removeAttr('style');;
            $(this).children().removeAttr('style');;
        });
    });
    // Product Grid
    $('#grid-view').on('click',function() {
        $('#grid-view').addClass('active');
        $('#list-view').removeClass('active');

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
            }
        })
    });

    // Обработчики событий для кнопок плюс и минус
    $('.buy_helper').bind("click", function(e) {
        if(e.offsetX < 10) {
            var quantInput = $(this).find('.quantity_input');
            var dataRatio = $(this).find('.quantity_input').attr('data-ratio');
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

            if (val > 0) {
                quantInput.val(val - ratio);
            }
        } else {
            var quantInput = $(this).find('.quantity_input');
            var dataRatio = $(this).find('.quantity_input').attr('data-ratio');
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
        }
    });
});