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


if ($_GET['tst']) {
    $arFilter = ['IBLOCK_ID' => 1, 'ID' => $arResult['ID']];
    $dbList = CIBlockSection::GetList([], $arFilter, false, ['UF_GROUP_PROP']);
    $groupPropValue = '';
    while($arRes = $dbList->Fetch()) {
        $groupPropValue = $arRes['UF_GROUP_PROP'];
    }

    $groupProp = [];
    if ($groupPropValue != '') {
        $groupProp = explode(',', $groupPropValue);
    }

    // Сформируем товары в разделе
    if (count($groupProp) > 0) {
        foreach ($groupProp as $key => $value) {
            $groupProp[$key] = trim($value);
        }

        // Получаем имя свойства
        $groupMap = [];
        foreach ($groupProp as $groupCode) {
            $iblockId = 1;
            $properties = CIBlockProperty::GetList(
                ['sort' => 'asc', 'name' => 'asc'],
                ['ACTIVE' => 'Y', 'IBLOCK_ID' => $iblockId, 'CODE' => $groupCode]
            );
            while ($prop_fields = $properties->GetNext()) {
                $groupMap[$groupCode] = $prop_fields['NAME'];
            }
        }
        $arResult['GROUP_MAP'] = $groupMap;

        // Формируем фильтр
        $filter = [
            'IBLOCK_ID' => 1,
            'ACTIVE' => 'Y',
            'IBLOCK_SECTION_ID' => $arResult['ID'],
        ];
        $logicFilter = ['LOGIC' => 'OR'];
        foreach ($groupProp as $prop) {
            $logicFilter[] = ['!PROPERTY_' . $prop => false];
        }
        $filter[] = $logicFilter;

        // Формируем поля для select
        $select = ['ID', 'NAME', 'PROPERTY_ARTICUL', 'PROPERTY_UNITS', 'PROPERTY_DIAMETER',
            'PROPERTY_LENGTH', 'PROPERTY_NAIMENOVANIE', 'PROPERTY_VES1000PS', 'PROPERTY_PRICE_OPT',
            'PROPERTY_PRICE_OPT2', 'PROPERTY_PRICE', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_TipSkladskogoZapasa',
            'PROPERTY_Ostatok', 'PROPERTY_UPAKOVKA', 'PROPERTY_UPAKOVKA2', 'PROPERTY_Svobodno', 'PROPERTY_Kratnost'];
        foreach ($groupProp as $prop) {
            $select[] = 'PROPERTY_' . $prop;
        }

        $dbResult = CIBlockElement::GetList(
            [],
            $filter,
            false,
            false,
            $select
        );

        $groupedItems = [];
        $arResultItems = array_column($arResult['ITEMS'], NULL,'ID');

        $productMap = array_column($arResult['ITEMS'], NULL, 'ID');
        while($result = $dbResult->Fetch()) {
            foreach ($groupProp as $prop) {
                $groupedItems[$prop][$result['ID']]['GROUP']= $result['PROPERTY_' . $prop . '_VALUE'];
                $groupedItems[$prop][$result['ID']]['ELEMENT'] = $result;
                $groupedItems[$prop][$result['ID']]['ELEMENT']['DETAIL_PAGE_URL'] = $productMap[$result['ID']]['DETAIL_PAGE_URL'];
            }
        }

        $arResult['GROUP_ITEMS'] = $groupedItems;
    }
}