<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

if ((int)$_GET['idBasketElement'] > 0) {
    \Bitrix\Main\Loader::includeModule('sale');

    $basket_storage = \Bitrix\Sale\Basket\Storage::getInstance(Bitrix\Sale\Fuser::getId(), SITE_ID);
    $basket = $basket_storage->getBasket();

    $basketProducts = [];
    foreach ($basket as $basket_item) {
        $product = $basket_item->getFieldValues();
        if ($product['PRODUCT_ID'] == $_GET['idBasketElement']) {
            $idForDelete = $product['ID'];
        }
    }

    if ((int)$idForDelete > 0) {
        $basket->getItemById($idForDelete)->delete();
        $basket->save();
    }
}
echo getBasketInfo();
die();