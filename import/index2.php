<?php
/**
 * Новый импорт
 */

set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
set_time_limit(0);

require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/import/Import.php';

if ($_GET['start'] == "gogo") {
    $obImport = new Import();

    // Пытаемся вызвать импорт максимум 5 раз, чтобы не повесить сайт
    $i = 0;
    while ($i < 5) {
        $i++;

        if ($obImport->startImport()) {
            echo('Импорт завершен ' . date("d.m.Y H:i:s"));

            break;
        }
    }
}