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

// Определяем картинку и название для товара
$dbGroups = \CIBlockElement::GetElementGroups($arResult['ITEM']['ID'], true);
$sections = false;

while($group = $dbGroups->Fetch()) {
    // TODO убрать строгое значение!
    if ($group['ID'] != 5966) {
        $sectionId = $group['ID'];
    }
}

if ($sectionId) {
    $result = CIBlockSection::GetList(
        [],
        [
            'IBLOCK_ID' => 1,
            '=ID' => $sectionId
        ],
        false,
        ['ID', 'PICTURE', 'CODE', 'UF_TEMPLATE']
    );

    $sectionParams = [];
    if ($section = $result->fetch()) {
        $sectionParams = $section;
    }

    // TODO костыль для раздела пистолеты, П.С. разобраться почему именно для этого раздела не подтягивается картинка
    if ($sectionParams['CODE'] == 'pistolet_termokleevoy') {
        $arResult['ITEM']['PREVIEW_PICTURE'] = CFile::ResizeImageGet($sectionParams['PICTURE'], ['width' => 900, 'height' => 600], BX_RESIZE_IMAGE_PROPORTIONAL, true);
        $arResult['ITEM']['PREVIEW_PICTURE']['SRC'] = $arResult['ITEM']['PREVIEW_PICTURE']['src'];
    }

    if (!empty($sectionParams) && $sectionParams['UF_TEMPLATE'] == 1 && $sectionParams['PICTURE']) {
        $arResult['ITEM']['PREVIEW_PICTURE'] = CFile::ResizeImageGet($sectionParams['PICTURE'], ['width' => 900, 'height' => 600], BX_RESIZE_IMAGE_PROPORTIONAL, true);
        $arResult['ITEM']['PREVIEW_PICTURE']['SRC'] = $arResult['ITEM']['PREVIEW_PICTURE']['src'];
    }

    if (!$arResult['ITEM']['PREVIEW_PICTURE']) {
        $arResult['ITEM']['PREVIEW_PICTURE'] = CFile::ResizeImageGet($sectionParams['PICTURE'], ['width' => 900, 'height' => 600], BX_RESIZE_IMAGE_PROPORTIONAL, true);
        $arResult['ITEM']['PREVIEW_PICTURE']['SRC'] = $arResult['ITEM']['PREVIEW_PICTURE']['src'];
    }

    if ($sectionParams['UF_TEMPLATE'] == 1) {
        $arResult['ITEM']['NAME'] = $arResult['ITEM']['PROPERTIES']['Naimenovanie']['VALUE'];
    }
}

