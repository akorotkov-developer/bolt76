<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\UI\Extension;
Extension::load('ui.bootstrap4');
?>

<?$APPLICATION->IncludeComponent(
    "bitrix:system.auth.forgotpasswd",
    ".default",
    Array()
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>