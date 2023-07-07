<?php

define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);

use Bitrix\Main\Config\Option;
use StrprofiBackupCloud\UploadActivity;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

Loader::includeModule('strprofibackupcloud');

$request = Application::getInstance()->getContext()->getRequest();

/*$rowId = Option::get('strprofibackupcloud', "CUR_TASK_ID");*/

$rowId = $request->get('rowid');

$status = '';

if ((int)$rowId > 0) {
    $activity = new UploadActivity();
    $status = $activity->checkUploadStatus($rowId);
}

echo json_encode($status);