<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arFields = [
    'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    'ID' => $arResult['ORIGINAL_PARAMETERS']['SECTION_ID']
];
$obSections = CIBlockSection::GetList(
    ['SORT' => 'ASC'],
    $arFields,
    false,
    ['ID', 'PICTURE', 'DESCRIPTION']
);

$arSection = [];
if ($arRez = $obSections->Fetch()) {
    $arSection = $arRez;
}

$arResult['SECTION_DESCRIPTION'] = $arSection['DESCRIPTION'];

if (!empty($arResult['PREVIEW_PICTURE']['SRC'])) {
    $arResult['MORE_PHOTO_PICTURE']['ID'] = (int) $arResult['PREVIEW_PICTURE']['ID'];
    $arResult['MORE_PHOTO_PICTURE']['SRC'] =  $arResult['PREVIEW_PICTURE']['SRC'];
    $arResult['MORE_PHOTO_PICTURE']['WIDTH'] = 150;
    $arResult['MORE_PHOTO_PICTURE']['HEIGHT'] = 150;
} else if ($arSection['PICTURE']) {
    $arSection['PICTURE'] = CFile::GetFileArray($arSection['PICTURE']);
    $arResult['MORE_PHOTO_PICTURE']['ID'] = (int)$arSection['PICTURE']['ID'];
    $arResult['MORE_PHOTO_PICTURE']['SRC'] = $arSection['PICTURE']['SRC'];
    $arResult['MORE_PHOTO_PICTURE']['WIDTH'] = 150;
    $arResult['MORE_PHOTO_PICTURE']['HEIGHT'] = 150;
}

// Установка заголовка
global $APPLICATION;
$APPLICATION->SetTitle(($arResult['PROPERTIES']['Naimenovanie']['VALUE'] != '') ? $arResult['PROPERTIES']['Naimenovanie']['VALUE'] : $arResult['NAME']);

// Добавим фото если есть дополнительные фото в товаре
if (count($arResult['PROPERTIES']['PHOTOS']['VALUE']) > 0 && $arResult['PROPERTIES']['PHOTOS']['VALUE'] !== false) {
    $arPhotos = [];
    if (!empty($arResult['PREVIEW_PICTURE']['SRC'])) {
        $arPhotos[] = $arResult['PREVIEW_PICTURE']['SRC'];
    }

    foreach ($arResult['PROPERTIES']['PHOTOS']['VALUE'] as $photoId) {
        $arPhotos[] = \CFile::GetFileArray($photoId)['SRC'];
    }
}

$arResult['PHOTOS'] = $arPhotos;

/**
 * Получаем обычную и оптовую цену товара
 */
$allProductPrices = \Bitrix\Catalog\PriceTable::getList([
    "select" => ["*"],
    "filter" => [
        "=PRODUCT_ID" => $arResult['ID'],
    ],
    "order" => ["CATALOG_GROUP_ID" => "ASC"]
])->fetchAll();

$arResult['ALL_PRODUCT_PRICES'] = $allProductPrices;