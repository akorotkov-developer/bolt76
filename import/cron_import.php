<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..") . '/bitrix/www';

$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define('BX_NO_ACCELERATOR_RESET', true);
define('CHK_EVENT', true);
define('BX_WITH_ON_AFTER_EPILOG', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);
?>

<?php

require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/import/Import.php';

$obImport = new Import('/home/bitrix/www');

ob_start();

if ($obImport->startImport()) {
    echo('Импорт завершен ' . date("d.m.Y H:i:s"));
}

$sOutForLog = ob_get_contents();
ob_end_clean();

file_put_contents('/home/bitrix/www/import/log_import.txt', $sOutForLog);
?>

