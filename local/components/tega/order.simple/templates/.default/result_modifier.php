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

// Определение предзаполненных параметров пользователя
global $USER;
$rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), ['ID' => $USER->GetID()]); // выбираем пользователей
$arUserParams = [];
if ($arRes = $rsUsers->Fetch()){
    $arUserParams = $arRes;
}

$arResult['USER_DB_FIO'] = $arUserParams['LAST_NAME'] . ' ' . $arUserParams['NAME'] . ' ' . $arUserParams['SECOND_NAME'];
$arResult['USER_DB_PERSONAL_PHONE'] = $arUserParams['PERSONAL_PHONE'];
$arResult['USER_DB_PERSONAL_EMAIL'] = $arUserParams['EMAIL'];
