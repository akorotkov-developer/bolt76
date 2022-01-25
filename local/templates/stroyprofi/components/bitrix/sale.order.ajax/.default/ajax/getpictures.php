<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$obGridRow = $_REQUEST['obGridRow'];

// Добавим картинки и названия к товарам, у которых их нет по аналогии с корзиной
foreach ($obGridRow as $key => $arItem) {
    // Уберем ссылки на товары
    $obGridRow[$key]['data']['DETAIL_PAGE_URL'] = '';

    $bNoImage = false;
    $bNoName = false;

    if ($arItem['data']['PREVIEW_PICTURE_SRC'] == '') {
        $bNoImage = true;
    }

    if ($arItem['data']['NAME'] == '') {
        $bNoName = true;
    }

    if ($bNoImage || $bNoName) {
        $dbResult = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => '1',
                '=ID' => $arItem['data']['PRODUCT_ID'],
            ],
            false, false,
            [
                'ID', 'NAME', 'IBLOCK_SECTION_ID', 'PROPERTY_Naimenovanie'
            ]
        );

        $sName = '';
        $iSectionId = '';
        if ($arRes = $dbResult->Fetch()) {
            // Если у эелемента нет названия, то добавим его
            if ($bNoName) {
                if ($arRes['PROPERTY_Naimenovanie_VALUE'] != '') {
                    $sName = $arRes['PROPERTY_Naimenovanie_VALUE'];
                } else {
                    $sName = $arRes['NAME'];
                }

                $obGridRow[$key]['data']['NAME'] = $sName;
            }

            if ($bNoImage) {
                $iSectionId = $arRes['IBLOCK_SECTION_ID'];
            }
        }

        // Получаем картинку раздела
        if ($iSectionId != '' && $bNoImage) {
            $dbResult = CIBlockSection::GetList(
                array(),
                ['IBLOCK_ID' => 1, '=ID' => $iSectionId],
                false,
                ['ID', 'PICTURE']
            );

            $sPictureSrc = '';
            while ($arRes = $dbResult->Fetch()) {
                if ($arRes['PICTURE'] != '') {
                    $iPictureId = $arRes['PICTURE'];
                    $sPictureSrc = \CFile::GetFileArray($arRes['PICTURE'])['SRC'];
                }
            }
        }

        if ($sPictureSrc != '') {
            $obGridRow[$key]['data']['PREVIEW_PICTURE'] = $iPictureId;
            $obGridRow[$key]['data']['PREVIEW_PICTURE_SRC'] = $sPictureSrc;
            $obGridRow[$key]['data']['PREVIEW_PICTURE_SRC_2X'] = $sPictureSrc;
            $obGridRow[$key]['data']['PREVIEW_PICTURE_SRC_ORIGINAL'] = $sPictureSrc;
        }
    }
}

echo  json_encode($obGridRow);