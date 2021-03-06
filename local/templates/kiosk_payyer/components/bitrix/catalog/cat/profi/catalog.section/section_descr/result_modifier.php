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

    foreach ($arResult['ITEMS'] as $key => $arItem) {
        $arResult['ITEMS'][$key]['NAME'] = $arItem['PROPERTY_' . $iNaimenovanieId];
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

            $dbResult = CIBlockSection::GetList([$by => $order], $arFilter);
            while ($arRes = $dbResult->Fetch()) {
                $arSections[$arRes['ID']] = $arRes['NAME'];
            }

            foreach ($arResult['ITEMS'] as $key => $arItem) {
                $sFilteredSectName = ltrim($arSections[$arItem['~IBLOCK_SECTION_ID']], '1234567890');
                $sFilteredSectName = ltrim($sFilteredSectName);

                $arResult['ITEMS'][$key]['FILTER_SECTION_NAME'] = $sFilteredSectName;
            }

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