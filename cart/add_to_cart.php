<?php require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>

<?php
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
}
?>