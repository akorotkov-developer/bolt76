<?php
/**
 * Новый импорт
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
set_time_limit(0);

require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/import/Import.php';

if ($_GET['start'] == "gogo") {
    $obImport = new Import();

    if ($obImport->startImport()) {
        echo('Импорт завершен ' . date("d.m.Y H:i:s"));
    }
}