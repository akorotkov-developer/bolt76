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
}
