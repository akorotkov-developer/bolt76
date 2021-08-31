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
    ['ID', 'PICTURE']
);

$arSection = [];
if ($arRez = $obSections->Fetch()) {
    $arSection = $arRez;
}

if ($arSection['PICTURE']) {
    $arSection['PICTURE'] = CFile::GetFileArray($arSection['PICTURE']);
    $arResult['MORE_PHOTO_PICTURE']['ID'] = (int)$arSection['PICTURE']['ID'];
    $arResult['MORE_PHOTO_PICTURE']['SRC'] = $arSection['PICTURE']['SRC'];
    $arResult['MORE_PHOTO_PICTURE']['WIDTH'] = 150;
    $arResult['MORE_PHOTO_PICTURE']['HEIGHT'] = 150;
}

