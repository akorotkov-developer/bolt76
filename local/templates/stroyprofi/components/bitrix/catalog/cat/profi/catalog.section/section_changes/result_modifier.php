<?php
// Подставлять наименование вместо названия если это результаты фильтрации
global $APPLICATION;

$sCurPage = $APPLICATION->GetCurPage();
if (strpos($sCurPage, '/apply/') !== false) {

    if(CModule::IncludeModule('iblock'))
    {
        $dbResult = CIBlockProperty::GetList(
            [],
            ['IBLOCK_ID' => '1']
        );

        $iNaimenovanieId = false;
        while ($arResultProps = $dbResult->Fetch()) {
            if ($arResultProps['CODE'] == 'Naimenovanie') {
                $iNaimenovanieId = $arResultProps['ID'];
            }
        }
    }

    $arProductIds = array_column($arResult['ITEMS'], 'ID');

    $dbResult = CIBlockElement::GetList(
        [],
        [
            'IBLOCKI_ID' => 1,
            'ID' => $arProductIds
        ],
        false,
        false,
        ['ID', 'DETAIL_PAGE_URL']
    );

    $arItemsProducts = [];
    while($arResults = $dbResult->Fetch()) {
        $arItemsProducts[$arResults['ID']] = $arResults;
    }

    foreach ($arItemsProducts as $key => $arProduct) {
        $arItemsProducts[$key]['DETAIL_PAGE_URL'] = CIBlock::ReplaceDetailUrl($arProduct['DETAIL_PAGE_URL'], $arProduct, false, 'E');
    }

    foreach ($arResult['ITEMS'] as $key => $arItem) {
        $arResult['ITEMS'][$key]['DETAIL_PAGE_URL'] = $arItemsProducts[$arItem['ID']]['DETAIL_PAGE_URL'];
    }

    // Разделение результатов фильтрации по разделам
    if (strpos($sCurPage,'clear/apply') === false) {
        $arSectionIds = array_unique(array_column($arResult['ITEMS'], '~IBLOCK_SECTION_ID'));

        // Определям название всех секций элементов на текущей странице
        if (count($arSectionIds) > 0) {
            $arFilter = [
                'IBLOCK_ID' => 1,
                'ID' => $arSectionIds
            ];

            $dbResult = CIBlockSection::GetList([$by => $order], $arFilter, false, ['ID', 'NAME', 'PICTURE']);
            while ($arRes = $dbResult->Fetch()) {
                $arSections[$arRes['ID']]['NAME'] = $arRes['NAME'];

                if ($arRes['PICTURE'] && $arRes['PICTURE'] != '') {
                    $arSections[$arRes['ID']]['PICTURE'] = CFile::ResizeImageGet($arRes['PICTURE'], array('width' => 128, 'height' => 130), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                    $arSections[$arRes['ID']]['PICTURE_BIG'] = $file_big = CFile::ResizeImageGet($arRes["PICTURE"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                }
            }

            $arSectionsForCount = [];
            foreach ($arResult['ITEMS'] as $key => $arItem) {
                $sFilteredSectName = ltrim($arSections[$arItem['~IBLOCK_SECTION_ID']]['NAME'], '1234567890');
                $sFilteredSectName = ltrim($sFilteredSectName);

                $arResult['ITEMS'][$key]['FILTER_SECTION_NAME'] = $sFilteredSectName;
                $arSectionsForCount[] = $sFilteredSectName;

                $arResult['ITEMS'][$key]['PICTURE'] = $arSections[$arItem['~IBLOCK_SECTION_ID']]['PICTURE'];
                $arResult['ITEMS'][$key]['PICTURE_BIG'] = $arSections[$arItem['~IBLOCK_SECTION_ID']]['PICTURE_BIG'];
            }

            $arResult['SECTIONS_COUNT'] = array_unique($arSectionsForCount);
            $arResult['IS_FILTER'] = true;
        }
    }
}

/** Определяем цену для текущего пользователя */
$priceGroup = UserHelper::getPriceUserGroup();

if ($priceGroup == 'OPT_2') {
    $arResult['IS_OPT_2'] = true;
} elseif ($priceGroup == 'OPT_3') {
    $arResult['IS_OPT_3'] = true;
}

// Получаем картинки в случае, если товары находятся в разделе распродаж
if ($arResult['ID'] == 5966) {
    $arProductIds = array_column($arResult['ITEMS'], 'ID');

    $sectionMap = [];
    foreach ($arProductIds as $productId) {
        $db_groups = CIBlockElement::GetElementGroups($productId, true);
        while ($ar_group = $db_groups->Fetch()) {
            if ($ar_group['ID'] != 5966) {
                $sectionMap[$productId] = $ar_group['ID'];
            }
        }
    }

    $arFields = [
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        'ID' => array_unique(array_values($sectionMap))
    ];
    $obSections = CIBlockSection::GetList(
        ['SORT' => 'ASC'],
        $arFields,
        false,
        ['ID', 'PICTURE', 'DESCRIPTION']
    );

    $arSection = [];
    while ($arRez = $obSections->Fetch()) {
        $arSection[$arRez['ID']] = $arRez;
    }

    // Назначим картинки
    foreach ($arResult['ITEMS'] as $key => $arItem) {
        if (!$arItem['PREVIEW_PICTURE']) {
            $sectionId = $sectionMap[$arItem['ID']];
            $arResult['ITEMS'][$key]['PREVIEW_PICTURE']['ID'] = $arSection[$sectionId]['PICTURE'];
        }
    }
}

/** Получаем товары распродажи */
$dbResult = CIBlockElement::GetList(
    [],
    [
        'IBLOCK_ID' => 1,
        'SECTION_CODE' => 'rasprodazha',
        'ACTIVE' => 'Y'
    ],
    false,
    false,
    [
        'ID'
    ]
);

$arItemsSale = [];
while($result = $dbResult->Fetch()){
    $arItemsSale[] = $result['ID'];
}

$arResult['SALE_ITEMS'] = $arItemsSale;

if ($_GET['tst']) {
    $sCurPage = $APPLICATION->GetCurPage();
    if (strpos($sCurPage, '/filter/') !== false) {
        $list = \CIBlockSection::GetNavChain(false, $arResult['ID'], ['ID', 'NAME', 'DEPTH_LEVEL', 'CODE'], true);
        $navChain = [];
        foreach ($list as $v) {
            $APPLICATION->AddChainItem($v['NAME'],  '/catalog/' . $v['ID'] . '-' . $v['CODE']);
        }
    }
}