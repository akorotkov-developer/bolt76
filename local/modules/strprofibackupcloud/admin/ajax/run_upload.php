<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

// Уберем ограничение времени выполнения скрипта
set_time_limit(0);
// Отключаем прерывание скрипта при отключении клиента
ignore_user_abort(true);

use Bitrix\Main\Loader;
use Strprofi\Backup;
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