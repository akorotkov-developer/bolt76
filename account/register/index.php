<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\UI\Extension;
Extension::load('ui.bootstrap4');
?>

<div id="content" class="col-sm-12 col-sm-9">
    <h1 class="page-title">Регистрация</h1>
    <p>Если у вас уже есть аккаунт, пожалуйста <a href="/account/auth/" class="link_auth">авторизуйтесь</a>.</p>

    <?$APPLICATION->IncludeComponent("bitrix:main.register","",Array(
            "USER_PROPERTY_NAME" => "",
            "SEF_MODE" => "Y",
            "SHOW_FIELDS" => [
                'PERSONAL_BIRTHDAY',
                'PERSONAL_GENDER',
                'NAME',
                'LAST_NAME',
                'SECOND_NAME',
                'PHONE_NUMBER',
                'PERSONAL_COUNTRY',
                'PERSONAL_STATE',
                'PERSONAL_CITY',
                'PERSONAL_ZIP',
                'PERSONAL_STREET',
                'PERSONAL_NOTES',
            ],
            "REQUIRED_FIELDS" => Array('PHONE_NUMBER'),
            "AUTH" => "Y",
            "USE_BACKURL" => "Y",
            "SUCCESS_PAGE" => "successful.php",
            "SET_TITLE" => "Y",
            "USER_PROPERTY" => Array('UF_WHOLESALE'),
            "SEF_FOLDER" => "/",
            "VARIABLE_ALIASES" => Array()
        )
    );?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>