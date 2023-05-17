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
use StrprofiBackupCloud\LocalBackup;
use StrprofiBackupCloud\UploadActivity;
use StrprofiBackupCloud\Dump\Dump;

$request = Application::getInstance()->getContext()->getRequest();

Loader::includeModule('strprofibackupcloud');


$diskType = $request->getPost('disk_type');
if ($request->getPost('action') && $request->getPost('action') == 'start_upload'
    && $diskType != '') {

    $activity = new UploadActivity();
    $activity->startUpload($diskType);
} else if ($request->getPost('action') == 'rezerv_copy') {
    \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Начали делать резервную копию'], '', 'log.txt');

/*    $dump = new Dump();

    $paramsForDumpAll = [
        'lang' => 'ru',
        'process' => 'Y',
        'action' => 'start',
        'dump_bucket_id' => '0',
        'dump_all' => 'Y',
        'sessid' => bitrix_sessid()
    ];*/

    // TODO временный запуск
    $activity = new UploadActivity();
    $activity->startUpload('yadisk');

/*    $dump->createDump($paramsForDumpAll);*/
}

if ($request->getPost('action') == 'create_copy') {
    $localBackup = new LocalBackup();
    $localBackupFiles = $localBackup->getLocalBackups();
    $localBackup->delete($localBackupFiles);

    $params = [
        'NOW' => true
    ];

    $activity = new UploadActivity();
    $rowId = $activity->startUpload($diskType, $params);

    // Возвращаем $rowId для получения информации о прогрессе
    echo $rowId;
}