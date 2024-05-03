<?




?>
<script src="/local/templates/stroyprofi/js/jquery.shCircleLoader.js"></script>
<script src="/local/templates/stroyprofi/js/jquery.shCircleLoader-min.js"></script>
<link rel="stylesheet" href="/local/templates/stroyprofi/js/jquery.shCircleLoader.css">

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


<div id="shclDefault"></div>
<style>
    #shclDefault {
        position: absolute!important;
        left: 200px!important;
    }
</style>

<p><button class="edit_marquee">Бегущая строка</button></p>
<p><button class="download_excel">Сгенерировать и скачать прайс-лист (Excel)</button></p>
<p><button class="download">Сгенерировать и скачать прайс-лист (PDF)</button></p>
<p><button class="start_import">Запустить импорт</button></p>
<p><button class="auto_cache">Автокеширование</button></p>
<p><a href="/import/logs/log_import.txt" class="download_log_import" download="">Скачать лог импорта</a></p>

<script type="text/javascript">
    $('.edit_marquee').click(function() {
        window.open("/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2Finclude_file%2Ftop_marquee.php&site=s1&lang=ru&&filter=Y&set_filter=Y");
    });
    $('.start_import').click(function () {
        window.open("/import/index2.php?start=gogo", '');
    });
    $('.auto_cache').click(function () {
        window.open("/bitrix/admin/cache.php?lang=ru", '');
    });

    $('.download').click(function() {
        $('#shclDefault').show();
        $('#shclDefault').shCircleLoader();
        $.ajax({
            url: "/bitrix/gadgets/strprofi/price/clear_cache.php",
            success: function(data){
            }
        });

        function downloadFile() {
            var link = document.createElement('a');
            link.href = "/pricepdf/price.pdf";
            link.download = "price.pdf";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            $('#shclDefault').hide();
        }

        setTimeout(downloadFile, 10000);
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