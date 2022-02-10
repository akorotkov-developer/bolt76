<?php
$arElementIds = array_column($arResult["ITEMS"], 'ID');

$dbResult = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => '1', 'ID' => $arElementIds],
    false,
    false,
    ['ID', 'DETAIL_PAGE_URL']
);

$dbResult->SetUrlTemplates("#SITE_DIR#/catalog/#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#");

$arItems = [];
while($arElement = $dbResult->GetNext()){
    $arItems[$arElement['ID']] = $arElement;
}

foreach ($arResult["ITEMS"] as $key => $arItem) {
    $arResult["ITEMS"][$key]['DETAIL_PAGE_URL'] = $arItems[$arItem['ID']]['DETAIL_PAGE_URL'];
}
