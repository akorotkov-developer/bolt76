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

    ob_start();

    if ($obImport->startImport()) {
        echo('Импорт завершен ' . date("d.m.Y H:i:s"));
    }

    $sOutForLog = ob_get_contents();
    ob_end_clean();

    file_put_contents('log_import.txt', $sOutForLog);
}