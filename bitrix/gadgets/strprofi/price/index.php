<?




?>

<style type="text/css">
    p a{
        display: none;
    }
    p span{
        display: none;
    }
    .download_log_import {
        color: #000!important;
        border-radius: 2px;
        appearance: auto;
        writing-mode: horizontal-tb !important;
        font-style: ;
        font-variant-ligatures: ;
        font-variant-caps: ;
        font-variant-numeric: ;
        font-variant-east-asian: ;
        font-weight: ;
        font-stretch: ;
        font-size: ;
        font-family: ;
        text-rendering: auto;
        color: buttontext;
        letter-spacing: normal;
        word-spacing: normal;
        line-height: normal;
        text-transform: none;
        text-indent: 0px;
        text-shadow: none;
        display: inline-block;
        text-align: center;
        align-items: flex-start;
        cursor: default;
        box-sizing: border-box;
        background-color: buttonface;
        margin: 0em;
        padding: 1px 6px;
        border-width: 1px;
        border-style: outset;
        border-color: buttonborder;
        border-image: initial;
    }
</style>




<p><button class="download_excel">Скачать прайс-лист (Excel)</button></p>
<p><button class="download">Скачать прайс-лист</button></p>
<p><button class="start_import">Запустить импорт</button></p>
<p><button class="auto_cache">Автокеширование</button></p>
<p><a href="/import/logs/log_import.txt" class="download_log_import" download="">Скачать лог импорта</a></p>

<script type="text/javascript">
    $('.start_import').click(function () {
        window.open("/import/index2.php?start=gogo", '');
    });
    $('.auto_cache').click(function () {
        window.open("/bitrix/admin/cache.php?lang=ru", '');
    });

    $('.download').click(function() {
        $.ajax({
            url: "/bitrix/gadgets/strprofi/price/clear_cache.php",
            success: function(data){
            }
        });

        function openPrintWindow() {
            window.open("/price-print.php", '_blank');
        }

        setTimeout(openPrintWindow, 1000);
    });

    $('.download_excel').click(function() {
        $.ajax({
            url: "/price/index.php",
            success: function(data){
            }
        });

        function downloadPrice() {
            window.open("/price/price.xlsx", '_blank');
        }

        setTimeout(downloadPrice, 1000);
    });

    $('.clear').click(function (e) {

        if ($('.download').is('[disabled]')) {
            alert('Нельзя очистить кэш во время генерации прайс-листа');
            return false;
        }


        t = $(this);
        t.attr('disabled', 'disabled');
        t.html('Идет очистка кэша прайс-листа...');

        $.ajax({
            url: "/bitrix/gadgets/strprofi/price/clear_cache.php",
            success: function(data){
            }
        });

        function sayHi() {
            t.prop("disabled", false);
            t.html('Очистить кэш');
        }

        setTimeout(sayHi, 2000);
    });
    $('.generate').click(function (e) {

        t = $(this);

        t.addClass('active');
        t.html('Генерируется прайс-лист...').attr('disabled', 'disabled');
        g = {}
        $.get('/price-print.php', g, function () {
            t.removeClass('active').removeAttr('disabled').hide();
            $('.download').show();
            $('.clear').html('Очистить кэш').removeAttr('disabled');
        })
    });

    function UrlExists(url) {
        var http = new XMLHttpRequest();
        http.open('HEAD', url, false);
        http.send();
        return http.status != 404;
    }
</script>