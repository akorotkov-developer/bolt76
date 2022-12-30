<?php

define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);

require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

// Уберем ограничение времени выполнения скрипта
set_time_limit(0);
// Отключаем прерывание скрипта при отключении клиента
ignore_user_abort(true);

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use StrprofiBackupCloud\UploadActivity;

// TODO УБРАТЬ ЭТО!!! ПЕРЕНЕСТИ КЛАСС В МОДУЛЬ !!!
require($_SERVER['DOCUMENT_ROOT']  . '/local/include/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/classes/general/backup.php");
Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Strprofi\Backup' => '/local/php_interface/include/lib/backup/Backup.php'
]);

$request = Application::getInstance()->getContext()->getRequest();

Loader::includeModule('strprofibackupcloud');


$diskType = $request->getPost('disk_type');
if ($request->getPost('action') && $request->getPost('action') == 'start_upload'
    && $diskType != '') {

    $activity = new UploadActivity();
    $activity->startUpload($diskType);

/*    $obBackup = new StrProfiBackup();
    $token = $obBackup->getToken();

    $obBackup = new Backup($token);

    // TODO тестовая отправка
    $obBackup->uploadToYaDisk(46);*/
}