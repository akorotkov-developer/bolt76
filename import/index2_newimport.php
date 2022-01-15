<?php
/**
 * Новый импорт
 */

set_time_limit();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
set_time_limit();

require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/import/Import.php';

$obImport = new Import();

$obImport->startImport();