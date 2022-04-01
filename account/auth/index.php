<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\UI\Extension;
Extension::load('ui.bootstrap4');
?>

    <div id="content" class="col-xs-6 col-sm-3 col-md-3">
        <?php if ($_GET['confirm_registration'] == 'yes') {

            $APPLICATION->IncludeComponent("bitrix:system.auth.confirmation",
                "",
                array(
                    "USER_ID" => 'confirm_user_id',
                    "CONFIRM_CODE" => 'confirm_code',
                    "LOGIN" => 'login'
                )
            );
        } ?>

        <? $APPLICATION->IncludeComponent("bitrix:system.auth.form", "", array(
                "REGISTER_URL" => "/account/register/",
                "FORGOT_PASSWORD_URL" => "/account/forgot/",
                "PROFILE_URL" => "/personal/",
                "SHOW_ERRORS" => "Y"
            )
        ); ?>

        <?php
        global $USER;
        if ($_GET['login'] == 'yes' && $USER->IsAuthorized()) {
            LocalRedirect('/catalog/');
        }?>
    </div><!-- /#content -->

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>