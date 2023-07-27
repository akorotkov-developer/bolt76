<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CJSCore::Init();
?>

<div id="dialog-content" class="bx-system-auth-form form-popup">

    <?
    if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR'])
        ShowMessage($arResult['ERROR_MESSAGE']);
    ?>

    <? if ($arResult["FORM_TYPE"] == "login"): ?>

        <form name="system_auth_form<?= $arResult["RND"] ?>" class="form-container" method="post" target="_top"
              action="<?= $arResult["AUTH_URL"] ?>">
            <? if ($arResult["BACKURL"] <> ''): ?>
                <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>"/>
            <? endif ?>
            <? foreach ($arResult["POST"] as $key => $value): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= $value ?>"/>
            <? endforeach ?>
            <input type="hidden" name="AUTH_FORM" value="Y"/>
            <input type="hidden" name="TYPE" value="AUTH"/>
            <h1>Вы не авторизованы</h1>
            <div class="form-group">
                <input type="text" placeholder="Введите Логин" class="form-control" id="login" name="USER_LOGIN"
                       maxlength="50" value="" size="17"/>
            </div>
            <script>
                BX.ready(function () {
                    var loginCookie = BX.getCookie("<?=CUtil::JSEscape($arResult["~LOGIN_COOKIE_NAME"])?>");
                    if (loginCookie) {
                        var form = document.forms["system_auth_form<?=$arResult["RND"]?>"];
                        var loginInput = form.elements["USER_LOGIN"];
                        loginInput.value = loginCookie;
                    }
                });
            </script>
            <div class="password">
                <input type="password" id="password-input" class="form-control" placeholder="Введите Пароль" name="USER_PASSWORD" maxlength="255" size="17" autocomplete="off"/>
                <a href="#" class="password-control"></a>
            </div>
            <script src="https://snipp.ru/cdn/jquery/2.1.1/jquery.min.js"></script>
            <script>
                $('body').on('click', '.password-control', function(){
                    if ($('#password-input').attr('type') == 'password'){
                        $(this).addClass('view');
                        $('#password-input').attr('type', 'text');
                    } else {
                        $(this).removeClass('view');
                        $('#password-input').attr('type', 'password');
                    }
                    return false;
                });
            </script>
            <? if ($arResult["SECURE_AUTH"]): ?>
                <span id="bx_auth_secure<?= $arResult["RND"] ?>"
                      title="<? echo GetMessage("AUTH_SECURE_NOTE") ?>" style="display:none">
                                </span>
                <noscript>
                                    <span class="bx-auth-secure" title="<? echo GetMessage("AUTH_NONSECURE_NOTE") ?>">
                                        <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                                    </span>
                </noscript>
                <script type="text/javascript">
                    document.getElementById('bx_auth_secure<?=$arResult["RND"]?>').style.display = 'inline-block';
                </script>
            <? endif ?>
            <div class="pass_forgot">
                <a class="pass_forgot" href="/account/forgot/?forgot_password=yes&backurl=%2Faccount%2Fauth%2F">Забыли
                    пароль?</a>
            </div>
            <input type="submit" class="btn btn-primary btn-popup" name="Login"
                   value="<?= GetMessage("AUTH_LOGIN_BUTTON") ?>"/>
            <input type="button" onclick="window.location.href='/account/register/index.php';" class="btn btn-popup"
                   value="Зарегистрироваться"/>
            <input type="button" class="btn btn-popup cancel continue_without_registration"
                   value="Продолжить без регистрации">
        </form>
    <? endif ?>
</div>
