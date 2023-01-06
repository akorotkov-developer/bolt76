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

$request = Application::getInstance()->getContext()->getRequest();

Loader::includeModule('strprofibackupcloud');


$diskType = $request->getPost('disk_type');
if ($request->getPost('action') && $request->getPost('action') == 'start_upload'
    && $diskType != '') {

    $activity = new UploadActivity();
    $activity->startUpload($diskType);
}