<?php
$arBasketItems = [];
$arProductIds = [];

$dbBasketItems = CSaleBasket::GetList(
    [
        'NAME' => 'ASC',
        'ID' => 'ASC'
    ],
    [
        "FUSER_ID" => CSaleBasket::GetBasketUserID(),
        "LID" => SITE_ID,
        "ORDER_ID" => $arResult["ORDER_ID"]
    ],
    false,
    false,
    []
);

while ($arItems = $dbBasketItems->Fetch())
{
    $arBasketItems[] = $arItems;
    $arProductIds[] = $arItems['PRODUCT_ID'];
}

$arResult['BASKET_ITEMS'] = $arBasketItems;
