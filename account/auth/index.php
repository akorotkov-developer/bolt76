<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>

    <div id="content" class="col-sm-9">
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
                "FORGOT_PASSWORD_URL" => "/auth/",
                "PROFILE_URL" => "/personal/",
                "SHOW_ERRORS" => "Y"
            )
        ); ?>
    </div><!-- /#content -->

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>