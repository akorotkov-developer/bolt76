<?php
/**
 * Новый импорт
 */

set_time_limit();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
set_time_limit();

require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/import/Import.php';

if ($_GET['start'] != "gogo") {

    $obImport = new Import();

    $obImport->startImport();
}