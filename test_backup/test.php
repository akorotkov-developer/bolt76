<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Title");

define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'));

require(DOCUMENT_ROOT . '/local/include/vendor/autoload.php');


/*require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/lib/backup/Backup.php';*/
?>

<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/backup.php");


Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Strprofi\Backup' => '/local/php_interface/include/lib/backup/Backup.php'
]);

// Старый способ backup ов
use Strprofi\Backup;

use Bitrix\Main\Loader;

// Новый способ backup ов
use StrprofiBackupCloud\Backup as StrProfiBackup;

Loader::includeModule('strprofibackupcloud');

$obBackup = new StrProfiBackup();
$token = $obBackup->getToken();




$obBackup = new Backup($token);

// Получение списка backup ов
$backUps = $obBackup->getBackupSite();

// Копируем все бэкапы
if (is_array($backUps) && count($backUps) > 0) {
    foreach ($backUps as $backupItem) {
        $obBackup->uploadBackUp($backupItem);
        break;
    }
}

// Закачать backup
/*$uploadBackUp = $obBackup->uploadBackUp($backUps[0]);*/
?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>