<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$basket_storage = \Bitrix\Sale\Basket\Storage::getInstance(Bitrix\Sale\Fuser::getId(), SITE_ID);
$basket = $basket_storage->getBasket();

$basketProducts = [];
foreach ($basket as $basket_item) {
    $product = $basket_item->getFieldValues();
    $basketProducts[$product['PRODUCT_ID']] = [
        'ID' => $product['PRODUCT_ID'],
        'QUANTITY' => $product['QUANTITY']
    ];
}

echo json_encode($basketProducts);
die();