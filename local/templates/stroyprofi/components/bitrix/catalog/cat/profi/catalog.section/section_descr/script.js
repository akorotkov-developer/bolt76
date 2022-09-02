$( document ).ready(function() {
    // Добавлять товары в корзину по нажатию на enter
    $('.quantity_input').bind("keypress", function(e) {
        if (e.keyCode == 13) {
            $(this).parent().parent().parent().parent().siblings('.cart_td').find('.add_to_cart_one').trigger('click');

            e.preventDefault();
            return false;
        }
    });

    // Обработчики событий для кнопок плюс и минус
    $('.buy_helper').bind("click", function(e) {
        if(e.offsetX<0) {
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