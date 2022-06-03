$( document ).ready(function() {
    // Добавлять товары в корзину по нажатию на enter
    $('.quantity_input').bind("keypress", function(e) {
        if (e.keyCode == 13) {
            $(this).parent().parent().parent().parent().siblings('.cart_td').find('.add_to_cart_one').trigger('click');

            e.preventDefault();
            return false;
        }
    });
});