<?php require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>

<?php
/** Старый метод добавления товара в корзину */
/*
if (CModule::IncludeModule("sale"))
{
    $iProductId = key($_REQUEST['ITEM']);
    $iQuantity = $_REQUEST['ITEM'][$iProductId];

    if (!$iQuantity) {
        return false;
    }

    //Получаем продукт
    $dbRez = CCatalogProduct::GetList(
        [],
        ['PRODUCT_ID' => $iProductId]
    );

    $arProducts = [];
    while($arRez = $dbRez->Fetch()) {
        $arProducts = $arRez;
    }
    $productName = $arProducts['NAME'];

    $dbRez = CPrice::GetList(
        [],
        ['PRODUCT_ID' => $iProductId]
    );
    $arPricesItems = [];

    if ($arRez = $dbRez->Fetch())
    {
        $arPricesItems = $arRez;
    }

    $arFields = array(
        "PRODUCT_ID" => $iProductId,
        "PRODUCT_PRICE_ID" => $arPricesItems['ID'],
        "PRICE" => $arPricesItems['PRICE'],
        "CURRENCY" => "RUB",
        "QUANTITY" => $iQuantity,
        "LID" => LANG,
        "DELAY" => "N",
        "CAN_BUY" => "Y",
        "NAME" => $productName,
    );

    $isAdded = CSaleBasket::Add($arFields);

    if ($isAdded) {
        echo getBasketInfo();
    }
}*/

/** Новый метод добавления товара в корзину*/
Bitrix\Main\Loader::includeModule("catalog");

$iProductId = key($_REQUEST['ITEM']);
$iQuantity = $_REQUEST['ITEM'][$iProductId];

if (!$iQuantity) {
    return false;
}

$iQuantity = str_replace(',', '.', $iQuantity);

// Получаем артикул товара для добавления в корзину
$arFilter = Array(
    'IBLOCK_ID' => 1,
    '=ID' => $iProductId,
);

$dbResult = CIBlockElement::GetList(
    [],
    $arFilter,
    false,
    false,
    ['PROPERTY_ARTICUL']

);

$sArticul = '';
while($arResult = $dbResult->Fetch()) {
    $sArticul = $arResult['PROPERTY_ARTICUL_VALUE'];
}

$fields = [
    'PRODUCT_ID' => $iProductId, // ID товара, обязательно
    'QUANTITY' => $iQuantity, // количество, обязательно
    'PROPS' => [
        ['NAME' => 'Артикул', 'CODE' => 'ARTICUL', 'VALUE' => $sArticul],
    ],
];
$isAdded = Bitrix\Catalog\Product\Basket::addProduct($fields);

if ($isAdded->isSuccess()) {
    echo getBasketInfo();
}
?>