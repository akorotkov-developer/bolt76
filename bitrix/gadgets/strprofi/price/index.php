<?




?>

<style type="text/css">
    p a{
        display: none;
    }
    p span{
        display: none;
    }
</style>




<p><button class="download">Скачать прайс-лист</button></p>
<p><button class="start_import">Запустить импорт</button></p>
<p><button class="auto_cache">Автокеширование</button></p>


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
    })

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