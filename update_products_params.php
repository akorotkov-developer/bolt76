<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Компания СтройПрофи. О компании");
$APPLICATION->SetTitle("СтройПрофи");


use \Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('sale');

//1. Получаем все товары на сайте
$arFilter = [
    'IBLOCK_ID' => 1,
    'INCLUDE_SUBSECTIONS' => 'Y'
];

$dbRez = CIBlockElement::GetList(
    ['SORT'=>'ASC'],
    $arFilter,
    false,
    false,
    ['ID', 'PROPERTY_PRICE_OPT', 'PROPERTY_PRICE_OPT2', 'PROPERTY_PRICE', 'PROPERTY_OSTATOK']
);

$arItems = [];
while($arRez = $dbRez->Fetch())
{
    $arItems[] = $arRez;
}

//2. Получаем все цены на сайте
$dbRez = CPrice::GetList(
    [],
    []
);

$arPricesItems = [];
while ($arRez = $dbRez->Fetch())
{
    $arPricesItems[$arRez['PRODUCT_ID'] . '_' . $arRez['CATALOG_GROUP_ID']] = $arRez;
}

//4. Получаем все продукты на сайте
$dbRez = CCatalogProduct::GetList(
    [],
    []
);

$arProducts = [];
while($arRez = $dbRez->Fetch()) {
    $arProducts[$arRez['ID']] = $arRez;
}

//3. Переберем все товары и запишем туда цены и доступное количество
$PRICE_BASE_ID = 1; //Базовая цена
$PRICE_OPT_ID = 2; //Оптовая цена
$PRICE_OPT2_ID = 3; //Оптовая цена 2
foreach ($arItems as $arItem) {
    //Сначала проверим существует ли такой продукт в системе и если не существует, то создадим его
    if (!$arProducts[$arItem['ID']]) {
        \Bitrix\Catalog\Model\Product::add(
            [
                'ID' => $arItem['ID'],
                'VAT_ID' => 1, //выставляем тип ндс (задается в админке)
                'VAT_INCLUDED' => 'Y' //НДС входит в стоимость
            ]
        );
    }

    //Запишем цены
    //Базовая цена
    if ($arItem['PROPERTY_PRICE_VALUE']) {
        $arFields = [
            'PRODUCT_ID' => $arItem['ID'],
            "CATALOG_GROUP_ID" => $PRICE_BASE_ID,
            "PRICE" => $arItem['PROPERTY_PRICE_VALUE'],
            "CURRENCY" => "RUB",
        ];

        if ($arPricesItems[$arItem['ID'] . '_' . $PRICE_BASE_ID]) {
            CPrice::Update(
                $arPricesItems[$arItem['ID'] . '_' . $PRICE_BASE_ID]['ID'],
                $arFields
            );
        } else {
            CPrice::Add(
                $arFields
            );
        }
    }

    //Оптовая цена
    if ($arItem['PROPERTY_PRICE_OPT_VALUE']) {
        $arFields = [
            'PRODUCT_ID' => $arItem['ID'],
            "CATALOG_GROUP_ID" => $PRICE_OPT_ID,
            "PRICE" => $arItem['PROPERTY_PRICE_OPT_VALUE'],
            "CURRENCY" => "RUB",
        ];
        if ($arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT_ID]) {
            CPrice::Update(
                $arItem['ID'],
                $arFields
            );
        } else {
            CPrice::Add(
                $arFields
            );
        }
    }

    //Оптовая цена 2
    if ($arItem['PROPERTY_PRICE_OPT2_VALUE']) {
        $arFields = [
            'PRODUCT_ID' => $arItem['ID'],
            "CATALOG_GROUP_ID" => $PRICE_OPT2_ID,
            "PRICE" => $arItem['PROPERTY_PRICE_OPT2_VALUE'],
            "CURRENCY" => "RUB",
        ];
        if ($arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT2_ID]) {
            CPrice::Update(
                $arItem['ID'],
                $arFields
            );
        } else {
            CPrice::Add(
                $arFields
            );
        }
    }

    //Записываем доступное количество товара
    CCatalogProduct::Update(
        $arItem['ID'],
        ['QUANTITY' => $arItem['PROPERTY_OSTATOK_VALUE']]
    );
}
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>