<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<?php
global $USER;
$USER->Authorize(87); // авторизуем покупателя киоска

// Очистка корзины
$isClear = CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID(), false);

LocalRedirect("/");
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>