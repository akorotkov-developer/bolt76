<?php
    global $USER;
    $arGroups = [];

    $rsGroups = \CUser::GetUserGroupEx($USER->GetID());
    while($arGroup = $rsGroups->GetNext()) {
        $arGroups[] = $arGroup['STRING_ID'];
    }

    $isKioskBuyer = false;
    if (in_array('KIOSK_BUYER', $arGroups) || strpos($_SERVER['HTTP_USER_AGENT'], 'KioskBrowser') !== false) {
        $isKioskBuyer = true;
    }
?>
<script>
    var isKioskBuyer = '<?php echo CUtil::PhpToJSObject($isKioskBuyer)?>';
</script>

<?php
if ($isKioskBuyer) {
    ?>
    <script type="text/javascript" src='<?= SITE_TEMPLATE_PATH?>/plugins/idltimer/idle-timer.js'></script>
    <script>
        $(function() {
            $( document ).idleTimer( 180000 );

            $( document ).on( "idle.idleTimer", function(event, elem, obj){
                $(location).attr('href', '/kiosk_auth/');
            });
        });
    </script>
<?php
}
?>

</div></td>
</tr>
</table>
<div class="clear"></div>
</div>
</div>
    <div class="footer">
        <div class="width_wrapper">
            <div class="dark_line bottom">
                <div class="corners left"><div class="right"></div></div>
                <div class="content">
                    <div class="shurup shurup_left"></div>
                    <div class="shurup shurup_right"></div>
                    <table class="footer-table">
                        <tr>
                            <td>
                                <div class="copyright">
                                 Строй Profi, 2012–<?=date("Y");?>
                                </div>
                            </td>
                            <td>
                                <div class="payments_logs">
                                    <img src="/images/logo-visa.png" alt="Логотипы платежных систсем">
                                </div>
                            </td>
                            <td><div class="menu">
	                            <?$APPLICATION->IncludeComponent(
	                            "bitrix:menu",
	                            "simple",
	                            Array(
		                            "ROOT_MENU_TYPE" => "bottom",
		                            "MAX_LEVEL" => "1",
		                            "CHILD_MENU_TYPE" => "left",
		                            "USE_EXT" => "N",
		                            "DELAY" => "N",
		                            "ALLOW_MULTI_SELECT" => "N",
		                            "MENU_CACHE_TYPE" => "A",
		                            "MENU_CACHE_TIME" => "3600",
		                            "MENU_CACHE_USE_GROUPS" => "N",
		                            "MENU_CACHE_GET_VARS" => array()
	                            ),
	                            false
                            );?>
                            </div></td>
                            <td><!--<a class="prominado" href="http://wwww.prominado.com/" title="Создание сайта – Студия «Проминадо»">разработка сайта</a>--></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
global $APPLICATION;
$curDir = $APPLICATION->GetCurDir();
?>
<div class="mobile-bottom-nav">
    <nav class="mobile-navbar">
        <ul class="mobile-menu-list">
            <li class="mobile-menu-item">
                <a href="/"
                   class="mobile-menu-link <?= $curDir == '/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-home"></i>
                    <span class="mobile-menu-name">Главная</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/catalog/"
                   class="mobile-menu-link <?= $curDir == '/catalog/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-search"></i>
                    <span class="mobile-menu-name">Каталог</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/personal/cart/"
                   class="mobile-menu-link  <?= $curDir == '/personal/cart/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-cart"></i>
                    <span class="mobile-menu-name">Корзина</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/personal/wishlist/"
                   class="mobile-menu-link  <?= $curDir == '/personal/wishlist/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-heart"></i>
                    <span class="mobile-menu-name">Избранное</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/personal/private/"
                   class="mobile-menu-link <?= $curDir == '/personal/private/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-contact"></i>
                    <span class="mobile-menu-name">Профиль</span>
                </a>
            </li>
        </ul>
    </nav>
</div>


<?php
global $APPLICATION;
$curDir = $APPLICATION->GetCurDir();
?>
<div class="mobile-bottom-nav">
    <nav class="mobile-navbar">
        <ul class="mobile-menu-list">
            <li class="mobile-menu-item">
                <a href="/"
                   class="mobile-menu-link <?= $curDir == '/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-home"></i>
                    <span class="mobile-menu-name">Главная</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/catalog/"
                   class="mobile-menu-link <?= $curDir == '/catalog/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-search"></i>
                    <span class="mobile-menu-name">Каталог</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/personal/cart/"
                   class="mobile-menu-link  <?= $curDir == '/personal/cart/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-cart"></i>
                    <span class="mobile-menu-name">Корзина</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/personal/wishlist/"
                   class="mobile-menu-link  <?= $curDir == '/personal/wishlist/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-heart"></i>
                    <span class="mobile-menu-name">Избранное</span>
                </a>
            </li>
            <li class="mobile-menu-item">
                <a href="/personal/private/"
                   class="mobile-menu-link <?= $curDir == '/personal/private/' ? 'is-active' : ''?>"
                >
                    <i class="mobile-menu-icon ion-md-contact"></i>
                    <span class="mobile-menu-name">Профиль</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Куки -->
<!--<div id="cookieBanner" class="cookie-banner">-->
<!--    <p>Мы используем cookies для улучшения работы сайта и анализа трафика. Продолжая использовать сайт, вы соглашаетесь с <a href="/politika-obrabotki-personalnyh-dannyh" target="_blank">Политикой конфиденциальности</a>.</p>-->
<!--    <button class="accept-btn" onclick="acceptCookies()">Принять все</button>-->
<!--    <button class="reject-btn" onclick="rejectCookies()">Отклонить все</button>-->
<!--    <button class="settings-btn" onclick="showCookieSettings()">Настроить</button>-->
<!--</div>-->

<!-- Модальное окно настроек cookies -->
<!--<div id="cookieSettings" class="cookie-settings">-->
<!--    <div class="cookie-settings-content">-->
<!--        <h2>Настройки cookies</h2>-->
<!--        <label>-->
<!--            <input type="checkbox" id="essentialCookies" checked disabled>-->
<!--            Технические cookies (обязательные для работы сайта)-->
<!--        </label>-->
<!--        <label>-->
<!--            <input type="checkbox" id="analyticsCookies">-->
<!--            Аналитические cookies (для анализа посещаемости)-->
<!--        </label>-->
<!--        <button class="save-btn" onclick="saveCookieSettings()">Сохранить</button>-->
<!--        <button class="close-btn" onclick="closeCookieSettings()">Закрыть</button>-->
<!--    </div>-->
<!--</div>-->

<!-- Yandex.Metrika counter -->
<!--<script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter216594 = new Ya.Metrika({id:216594, webvisor:true, clickmap:true}); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/216594" style="position:absolute; left:-9999px;" alt="" /></div></noscript>-->
<!-- /Yandex.Metrika counter -->
</body>
</html>
