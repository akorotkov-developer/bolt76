<?php
// Подставим картинку, если таковой нет
// Определи адрес детальной страницы

$iSectionId = $arResult['ITEM']['IBLOCK_SECTION_ID'];
$dbResult = CIBlockSection::GetList(
    array(),
    ['IBLOCK_ID' => 1, '=ID' => $iSectionId],
    false,
    ['ID', 'PICTURE', 'CODE']
);

$sPictureSrc = '';
$sSectionCode = '';
while ($arRes = $dbResult->Fetch()) {
    $sSectionCode = $arRes['CODE'];

    if ($arResult['ITEM']['PREVIEW_PICTURE']['ID'] == 0) {
        $arResult['ITEM']['PREVIEW_PICTURE']['SRC'] = \CFile::GetFileArray($arRes['PICTURE'])['SRC'];
        $arResult['ITEM']['PREVIEW_PICTURE_SECOND']['SRC'] = \CFile::GetFileArray($arRes['PICTURE'])['SRC'];
    }
}

// Составим адрес детальной страницы
$detailPageUrlTemplate = '/catalog/#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#';

$arReplaces = ["#SECTION_ID#", "#SECTION_CODE#", "#ELEMENT_CODE#"];
$arValues   = [$iSectionId, $sSectionCode, $arResult['ITEM']['CODE']];

$arResult['ITEM']['DETAIL_PAGE_URL'] = str_replace($arReplaces, $arValues, $detailPageUrlTemplate);