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
        $('#simple_order_form_INN').bind("change keyup input click", function() {
            if (this.value.match(/[^0-9]/g)) {
                this.value = this.value.replace(/[^0-9]/g, '');
            }
        });
        $('#simple_order_form_KPP').bind("change keyup input click", function() {
            if (this.value.match(/[^0-9]/g)) {
                this.value = this.value.replace(/[^0-9]/g, '');
            }
        });

        var sToken = "ed7f02d17e73afff8b0621a1b1b5a5a100d06672";
        $(".form-control-address").suggestions({
            token: sToken,
            type: "ADDRESS",
        });
        $("input[name='simple_order_form[COMPANY_ADR]']").suggestions({
            token: sToken,
            type: "ADDRESS",
        });
    }
}

$(document).ready(function() {
    $(document).on('click', '.switch', function() {
        var radiosPersonType = $('input:radio[name=PERSON_TYPE]');
        var paySystemBlock = $('input[name="simple_order_form[PAY_SYSTEM]"]');
        var labelPaySystem = $('label[for="pay_system_3"]').parent();

        $(this).toggleClass("switchOn");
        if ($(this).hasClass('switchOn')) {
            radiosPersonType.filter('[value=2]').attr('checked', true);
            radiosPersonType.trigger('change');
            labelPaySystem.fadeOut();
            paySystemBlock.filter('[value=2]').attr('checked', true);
        } else {
            radiosPersonType.filter('[value=1]').attr('checked', true);
            radiosPersonType.trigger('change');
            labelPaySystem.fadeIn();
        }
    });

    // При доставке до терминала не должно быть наличного расчета
    $(document).on('change', '.input_delivery', function() {
        var paySystemBlock = $('input[name="simple_order_form[PAY_SYSTEM]"]');
        var labelPaySystem = $('label[for="pay_system_3"]').parent();

        if ($(this).val() == 4) {
            labelPaySystem.fadeOut();
            paySystemBlock.filter('[value=2]').attr('checked', true);
        } else {
            labelPaySystem.fadeIn();
        }
    });
});
