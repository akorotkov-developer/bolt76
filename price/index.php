<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<?php
// Подключение библиотек и классов
require(__DIR__ . '/../local/include/vendor/autoload.php');
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/price/Price.php';

/**
 * Методы получения данных каталога
 */

$price = new Price();
$price->getPrice();
?>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
