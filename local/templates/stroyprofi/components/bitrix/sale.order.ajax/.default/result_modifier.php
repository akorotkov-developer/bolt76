<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);

// Добавим картинки и названия к товарам, у которых их нет по аналогии с корзиной
foreach ($arResult['JS_DATA']['GRID']['ROWS'] as $key => $arItem) {
    // Уберем ссылки на товары
    $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['DETAIL_PAGE_URL'] = '';

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
                'ID', 'NAME', 'IBLOCK_SECTION_ID', 'PROPERTY_NAIMENOVANIE'
            ]
        );

        $sName = '';
        $iSectionId = '';
        if ($arRes = $dbResult->Fetch()) {
            // Если у эелемента нет названия, то добавим его
            if ($bNoName) {
                if ($arRes['PROPERTY_NAIMENOVANIE_VALUE'] != '') {
                    $sName = $arRes['PROPERTY_NAIMENOVANIE_VALUE'];
                } else {
                    $sName = $arRes['NAME'];
                }

                $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['NAME'] = $sName;
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
            $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['PREVIEW_PICTURE'] = $iPictureId;
            $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['PREVIEW_PICTURE_SRC'] = $sPictureSrc;
            $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['PREVIEW_PICTURE_SRC_2X'] = $sPictureSrc;
            $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['PREVIEW_PICTURE_SRC_ORIGINAL'] = $sPictureSrc;
        }
    }
}