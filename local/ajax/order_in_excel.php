<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

/** Генерация заказа ввиде прайс-листа Excel */
require($_SERVER['DOCUMENT_ROOT'] . '/local/include/vendor/autoload.php');
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/OrderExcel.php';

use Bitrix\Main\Application;
$request = Application::getInstance()->getContext()->getRequest();
$sessionId = $request->get('session_id');
$delete = $request->get('delete');

if ($delete === 'Y') {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/order_excel/price_order_' . $sessionId . '.xlsx';

    if (file_exists($filePath)) {
        unlink($filePath);
    }
} else {
    $orderExcel = new StrProfi\OrderExcel($sessionId);
    try {
        $isGenerate = $orderExcel->generate();
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    }

    if ($isGenerate) {
        echo 'true';
    }
}