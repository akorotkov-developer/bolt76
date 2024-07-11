<?php
// Подставим картинку, если таковой нет
// Определи адрес детальной страницы

// Проверяем, не является ли раздел распродажей
$dbResult = CIBlockSection::GetList(
    [],
    [
        'IBLOCK_ID' => 1,
        'CODE' => 'rasprodazha',
    ],
    false,
    [
        'ID', 'CODE'
    ]
);
$saleSectionId = null;
if ($result = $dbResult->fetch()) {
    $saleSectionId = $result['ID'];
}

$dbGroups = CIBlockElement::GetElementGroups($arResult['ITEM']['ID'], true);
$curSectionId = [];
while($arGroup = $dbGroups->Fetch()) {
    if ($arGroup['ID'] != $saleSectionId) {
        $curSectionId = $arGroup['ID'];
    }
}
if ((int) $curSectionId > 0) {
    $arFields = [
        'IBLOCK_ID' => 1,
        'ID' => $curSectionId
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
}

if (empty($arResult['ITEM']['PREVIEW_PICTURE']['ID'])) {
    $arSection['PICTURE'] = \CFile::GetFileArray($arSection['PICTURE']);
    $arResult['ITEM']['PREVIEW_PICTURE']['ID'] = (int)$arSection['PICTURE']['ID'];
    $arResult['ITEM']['PREVIEW_PICTURE']['SRC'] = $arSection['PICTURE']['SRC'];
    $arResult['ITEM']['PREVIEW_PICTURE']['WIDTH'] = 150;
    $arResult['ITEM']['PREVIEW_PICTURE']['HEIGHT'] = 150;
    $arResult['ITEM']['PREVIEW_PICTURE_SECOND']['SRC'] = $arSection['PICTURE']['SRC'];
}





// Составим адрес детальной страницы
$detailPageUrlTemplate = '/catalog/#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#';

$arReplaces = ["#SECTION_ID#", "#SECTION_CODE#", "#ELEMENT_CODE#"];
$arValues   = [$iSectionId, $sSectionCode, $arResult['ITEM']['CODE']];

$arResult['ITEM']['DETAIL_PAGE_URL'] = str_replace($arReplaces, $arValues, $detailPageUrlTemplate);
