$(function () {

    $(".section-tabs").tabs(".section-panes > .section-pane", { history: true });

    $(".element_table td").hover(function (e) {
        if (!$(this).hasClass("section_description")) $(this).closest("tr").addClass("hover");
    }, function (e) {
        $(this).closest("tr").removeClass("hover");
    });
    $('.main_navigation a').click(function (e) {
        el = $(this);

        if ($(el).closest('.root-item-selected').length == 0) {
            //$('.root-item-selected ul').slideUp(100);
        }

        if ($(el).closest('.item-selected').length == 0) {
            //$('.item-selected ul').slideUp(100);
        }


        //$(el).next('ul').slideDown(100);

    });


    $('.buy .buy_helper').live('mouseenter', function () {
        $(this).addClass('hovered');
    });

    $('.buy .buy_helper').live('mouseleave', function () {
        $(this).removeClass('hovered');
    });


    prev = '';
    var request;
    $('.header .search .search-input').bind("keyup", function (e) {
        value = $(this).val();
        where = $(this).closest("form").find("input[name='where']:checked").val();
        if (value == '') {
            $('.header .search .search_close').click();
        } else {
            if (value != prev) {
                if (request) request.abort();
                request = $.get("/includes/search_helper.php", {q: value, where: where}, function (data) {
                    if (data != '') {
                        $('.header .search .search_results').html(data).slideDown();
                        $('.header .search .search_close').css({display: 'block'});
                    }
                });
                prev = value;
            }
        }


    });

    $('.main_navigation .close_block').click(function (e) {
        e.preventDefault();
        $('.left_block_holder .left_block').animate({'marginLeft': '-=600'}, function () {
            $('.left_block_holder').hide();
            $.get('/includes/menu_stat.php', {s: 'hide'});
            $('.page_content .open_left').show();
        });

    });

    $('.page_content .open_left').click(function (e) {
        $('.left_block_holder').show();
        $('.left_block_holder .left_block').animate({'marginLeft': '0'}, function () {
            $.get('/includes/menu_stat.php', {s: 'open'});
        });
        $('.page_content .open_left').hide();
    });

    $('.header .search .search_close').click(function (e) {
        e.preventDefault();
        $('.header .search .search_results').slideUp().html("");
        $(this).hide();
        $('.header .search .search-input').val('').blur();
    });

    $(".fancybox").fancybox();

    /*  $(window).resize(function(){
     $(".wrapper .page_content").css("height","auto");
     if($(window).height()>($(".wrapper .page_content").height()+233)){
     $(".wrapper .page_content").height($(window).height()-233);
     }else{
     $(".wrapper .page_content").css("height","auto");
     }
     }); */

    $('.element_table .delete a').bind('click', function (e) {
        e.preventDefault();
        el = $(this);
        //if(confirm("Удалить?")){
        $.post('/cart/remove.php', {id: $(el).data("id")}, function () {
            $(el).closest("tr").remove();
        });
        // }
    });


    $('.fancybox_detailed').live("click", function (e) {
            e.preventDefault();
            //$(this).closest('tr').next('.more_info_holder').find('td').slideToggle('slow');
            $.fancybox.open([
                {
                    maxWidth: "70%",
                    content: $('#' + $(this).data("id")).html(),
                    type: 'inline',
                    afterClose: function () {
                    }
                }
            ]);
        }
    );

    $('.help_values a').live('click', function (e) {
        e.preventDefault();
        oldVal = 0;
        if (t = parseInt($(this).closest('.buy_helper').find('.input_holder input').val())) {
            oldVal = t;
        }
        var newVal = oldVal + parseInt($(this).attr('data-val'));
        $(this).closest('.buy_helper').find('.input_holder input').val(newVal).trigger('change');

    });


    $('.order_form').submit(function (e) {
        e.preventDefault();
        el = $(this);
        $.post("/cart/add_to_cart.php", $(this).serialize(), function (data) {
            $('.cart_info_holder').html(data);
            $('.order_button a').text('Добавлено. Перейти в корзину').removeAttr('onclick');
            $(el).find('input').each(function (ind, e) {
                $(e).val('');
            });
            $('.order .order_precount').html('');
        })
    });


    $('.add_to_cart_one').live('click', function (e) {
        e.preventDefault();
        el = $(this);
        $.post("/cart/add_to_cart.php", $(el).closest('tr').find('.buy .input_holder input').serialize(), function (data) {
            $('.cart_info_holder').html(data);
            $(el).closest('tr').find('.buy .input_holder input').val('');
            recountForm();
            if ($(el).closest(".fancybox-overlay").length > 0) {
                $(".fancybox-close").click();
            }
            $("#notification").css("top", "0");
            $("#notification").animate({"top": "0"}, 400).delay(2000).animate({"top": "-70px"}, 400);
        })
    });


    $('.recount_cart').click(function (e) {
        e.preventDefault();
        $('#recount_cart').val("Y");
        $(this).closest('form').submit();
    });


    recountForm();
    $(".onblur").live("focus", function () {
        if ($(this).val() == $(this).attr("rel")) {
            $(this).val("");
            $(this).removeClass("blured");
        }
    });
    $(".onblur").live("blur", function () {
        if ($(this).val() == "") {
            $(this).val($(this).attr("rel"));
            $(this).addClass("blured");
        }
    });
    $("form").submit(function () {
        $(this).find(".onblur").each(function () {
            if ($(this).val() == $(this).attr("rel")) $(this).val("");
        });
    });
    converter();
});

function converter() {
    $('.buy .input_holder input').live('change', function (e) {
        recountForm();
        vesHelper($(this));
    });

    $('.buy .input_holder input').live('keyup', function (e) {
        recountForm();
        vesHelper($(this));
    });
    $('table.element_table td.opt, table.element_table td.roz').hover(
        function () {

            $('.vesHelperHolder', $(this)).show();
        },
        function () {
            $('.vesHelperHolder', $(this)).hide();
        }
    )
}

function vesHelper(t) {
    if (k = parseFloat(t.closest('.buy_helper').find('.vesHelper').attr("data-k"))) {
        var k_val = t.closest('.buy_helper').find('.vesHelper').attr("data-val");
        var num = parseFloat(t.val());
        if (!num) num = 0;
        var kv = parseInt(k * num);
        if (k_val=='кг') {
            kv = (k*num).toFixed(2)
        }

        //console.log(k);
        //console.log(num);
        //console.log(kv);

        t.closest('.buy_helper').find('.vesHelper').html("~ " + kv + " " + k_val);
        t.closest('.buy_helper').find('.vesHelper').attr("data-num", kv);
    }
}


function recountForm() {
    itogo = 0;
    itogoCount = 0;
    $('.buy .input_holder input').each(function (ind, el) {
        if ($(this).closest(".fancybox-overlay").length == 0) {
            price = parseFloat($(el).data('price'));

            if (count = parseInt($(el).val())) {
                itogo += price * count;
                itogoCount += count;
            }
        }
    });

    if (itogoCount > 0) {
        $('.order .order_precount').html("Количество: " + itogoCount.toString() + ", Сумма: " + itogo.toString() + " руб.");
    } else {
        $('.order .order_precount').html("");
    }

}

function formatNum(n) {
    var n = ('' + n).split('.');
    var num = n[0];
    var dec = n[1];
    var r, s, t;
    if (num.length > 3) {
        s = num.length % 3;
        if (s) {
            t = num.substring(0, s);
            num = t + num.substring(s).replace(/(\d{3})/g, " $1");
        } else {
            num = num.substring(s).replace(/(\d{3})/g, " $1").substring(1);
        }
    }
    if (dec && dec.length > 3) {
        dec = dec.replace(/(\d{3})/g, "$1 ");
    }
    return num + (dec ? '.' + dec : '');
}