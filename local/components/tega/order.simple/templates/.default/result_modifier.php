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
$arBasketItems = array_column($arBasketItems, NULL, 'PRODUCT_ID');

$dbResult = CIBlockElement::GetList(
    [],
    [
        'IBLOCK_ID' => 1,
        'ID' => $arProductIds
    ],
    false,
    false,
    [
        'ID', 'NAME', 'PROPERTY_SVOBODNO', 'PROPERTY_Svertka'
    ]
);

$arItems = [];
while($aRes = $dbResult->Fetch()) {
    if ((float)$aRes['PROPERTY_SVOBODNO_VALUE'] - (float)$arBasketItems[$aRes['ID']]['QUANTITY'] < 0) {
        $arItems[] = trim($aRes['PROPERTY_SVERTKA_VALUE']) . ' (Доступно: ' . (float)$aRes['PROPERTY_SVOBODNO_VALUE'] . ')';;
    }
}

if (count($arItems) > 0) {
    $arResult['NOT_AVAIL'] = $arItems;

    // Убираем онлайн оплату
    foreach ($arResult['PAY_SYSTEM'] as $key => $paySystem) {
        if ($paySystem['ID'] == 2) {
            unset($arResult['PAY_SYSTEM'][$key]);
        }
    }
}

// Определение предзаполненных параметров пользователя
global $USER;
if ($USER->IsAuthorized()) {
    $rsUsers = CUser::GetList(
        ($by = "personal_country"),
        ($order = "desc"),
        ['ID' => $USER->GetID()],
        ['SELECT' => ['UF_COMPANY_NAME', 'UF_YUR_ADDRESS', 'UF_INN', 'UF_KPP']]
    );

    // выбираем пользователей
    $arUserParams = [];
    if ($arRes = $rsUsers->Fetch()) {
        $arUserParams = $arRes;
    }

    $arResult['USER_DB_FIO'] = $arUserParams['LAST_NAME'] . ' ' . $arUserParams['NAME'] . ' ' . $arUserParams['SECOND_NAME'];
    $arResult['USER_DB_PERSONAL_PHONE'] = $arUserParams['PERSONAL_PHONE'];
    $arResult['USER_DB_PERSONAL_EMAIL'] = $arUserParams['EMAIL'];
    $arResult['USER_DB_COMPANY_NAME'] = $arUserParams['UF_COMPANY_NAME'];
    $arResult['USER_DB_YUR_ADDRESS'] = $arUserParams['UF_YUR_ADDRESS'];
    $arResult['USER_DB_INN'] = $arUserParams['UF_INN'];
    $arResult['USER_DB_KPP'] = $arUserParams['UF_KPP'];
}

