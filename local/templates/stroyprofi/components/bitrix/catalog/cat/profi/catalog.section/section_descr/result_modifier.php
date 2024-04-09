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
        $arResult['ITEMS'][$key]['NAME'] = $arItem['PROPERTY_' . $iNaimenovanieId];
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

            /**
             * Сортировка по алфавиту с группировкой по разделам
             */
            $groupSectionItems = [];
            foreach ($arResult['ITEMS'] as $key => $arItem) {
                $groupSectionItems[$arItem['~IBLOCK_SECTION_ID']][] = $arItem;
            }

            foreach ($groupSectionItems as $groupKey => $group) {
                usort($group, function($a, $b) {
                    return trim($a['PROPERTIES']['Naimenovanie']['VALUE']) <=> trim($b['PROPERTIES']['Naimenovanie']['VALUE']);
                });
                $groupSectionItems[$groupKey] = $group;
            }

            $arResult['ITEMS'] = [];


            foreach ($groupSectionItems as $group) {
                foreach ($group as $item) {
                    $arResult['ITEMS'][] = $item;
                }
            }

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