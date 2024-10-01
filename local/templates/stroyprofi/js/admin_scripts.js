$( document ).ready(function() {
    $(document).on('click', '.get_link_for_sber_pay' , function() {
        var orderId = $(this).attr('data-orderid');
        var popup = new BX.CDialog({
            'title': 'Ссылка на оплату',
            'content': '<span style="color: #FF7920; font-weight: bold; font-size: 18px;">https://strprofi.ru/sber_pay.php?ORDER_ID=' + orderId + '&PAYMENT_ID=2</span>',
            'draggable': true,
            'resizable': true,
            'buttons': [BX.CDialog.btnClose]
        });
        popup.Show();
    });

    $(document).on('click', '.send_changed_order' , function() {
        var orderId = $(this).attr('data-orderid');
        console.log('orderId');
        console.log(orderId);

        var btn_send = {
            title: 'Отправить',
            id: 'savesend',
            name: 'savesend',
            className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
            action: function () {
                $.ajax({
                    url: '/local/ajax/send_order.php',
                    method: 'get',
                    data: {orderId: orderId},
                    success: function(data) {
                        alert('Письмо отправлено');
                    }
                });

                this.parentWindow.Close();
            }
        };

        var popup = new BX.CDialog({
            'title': 'Повторная отправка письма клиенту',
            'content': '<span style="color: #000; font-weight: bold; font-size: 18px;">Уверены, что хотите повторно отпрвить письмо клиенту с деталями заказа?</span>',
            'draggable': true,
            'resizable': true,
            'buttons': [btn_send, BX.CDialog.btnCancel]

        });
        popup.Show();
    });
});