$( document ).ready(function() {
    $(document).on('click', '.input_delivery', function(){
        if ($(this).val() == '3') {
            $('.b-label-address').fadeIn();
            $('.form-control-address').prop('required', true);
        } else {
            $('.b-label-address').fadeOut();
            $('.form-control-address').prop('required', false);
        }
    });

    $nav = $('.fixed-div');
    $widthetalon = $('.width-etalon');
    $window = $(window);
    $h = $nav.offset().top;
    $window.scroll(function(){
        $nav.css('width', $widthetalon.outerWidth() - 30);

        if ($window.scrollTop() > $h) {
            $nav.addClass('fixed');
        } else {
            $nav.removeClass('fixed');
        }
    });

    $('#submitbtn').on('click', function() {
        $('input[required]').addClass('req_fail');
    });
});

var objOrderForm = {
    init: function () {
        $("input[id='simple_order_form_PHONE']").mask("+7(999) 999-9999");

        var sToken = "ed7f02d17e73afff8b0621a1b1b5a5a100d06672";
        $(".form-control-address").suggestions({
            token: sToken,
            type: "ADDRESS",
        });
    }
}