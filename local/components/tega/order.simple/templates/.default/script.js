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
        $("input[id='simple_order_form_RECIPIENT_PHONE']").mask("+7(999) 999-9999");
        $("input[id='simple_order_form_CONTACT_PHONE_RECIPIENT']").mask("+7(999) 999-9999");

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
        $("input[name='simple_order_form[TERMINAL_ADDRESS]']").suggestions({
            token: sToken,
            type: "ADDRESS",
        });
        $("input[name='simple_order_form[INN]']").suggestions({
            token: sToken,
            type: "party",
            onSelect: function(suggestion) {
                $("input[name='simple_order_form[INN]']").val(suggestion.data.inn);
                $("input[name='simple_order_form[KPP]']").val(suggestion.data.kpp);
                $("input[name='simple_order_form[COMPANY]']").val(suggestion.data.name.short_with_opf);
                $("input[name='simple_order_form[COMPANY_ADR]']").val(suggestion.data.address.value);

                console.log(suggestion.data);
            }
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
            paySystemBlock.filter('[value=5]').attr('checked', true);
            paySystemBlock.trigger('change');
        } else {
            radiosPersonType.filter('[value=1]').attr('checked', true);
            radiosPersonType.trigger('change');
            labelPaySystem.fadeIn();
        }
    });

    // При доставке до терминала не должно быть наличного расчета
    // Должны быть видны данные для доставки
    $(document).on('change', '.input_delivery', function() {
        var paySystemBlock = $('input[name="simple_order_form[PAY_SYSTEM]"]');
        var labelPaySystem = $('label[for="pay_system_3"]').parent();

        if ($(this).val() == 4) {
            labelPaySystem.fadeOut();
            paySystemBlock.filter('[value=2]').attr('checked', true);

            // Показываем блок с данными о доставке
            $('.b-transport-info').fadeIn();
            $('#simple_order_form_TRANSPORT_COMPANY').val($('input[name="delivery_company_name"]:checked').val());
        } else {
            labelPaySystem.fadeIn();
            $('.b-transport-info').fadeOut();
            $('#simple_order_form_TRANSPORT_COMPANY').val('');
        }
    });

    // Переключение названий компаний доставки
    $(document).on('change', 'input[name="delivery_company_name"]', function() {
        $('#simple_order_form_TRANSPORT_COMPANY').val($(this).val());
    });

    // Запрет отправки формы по нажатию Enter
    $('#simple_order_form').bind("keypress", function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    // Выбор типа доставки при выборе доставкой до терминала транспортной компанией
    $(document).on('change', 'input[name="delivery_type"]', function() {
        $('#address_for_delivery_type').text($(this).val());
    });

    // Событие выбора времени доставки в select
    $(document).on('change', 'select#choose_delivery_time', function() {
        $('#simple_order_form_DESIRED_DELIVERY_TIME').val($(this).val());
    });
});

// Загрузка файла на сервер
function downloadFile(obj) {
    var obFile = obj.files[0];
    var form_data = new FormData;
    form_data.append("file", obFile);
    $.ajax({
        url: sTemplateFolder + "/downloadfile.php",
        type: "POST",
        processData: false,
        contentType:false,
        async:false,
        data: form_data,
        success:function(data) {
            $('input[name="simple_order_form[FILE_WITH_BANKING_DETAILS]"]').val(data);
            $('#file_name').html(obj.files[0].name);
        }
    });
};