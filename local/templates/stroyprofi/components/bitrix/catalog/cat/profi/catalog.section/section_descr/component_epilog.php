<?php
if (CModule::IncludeModule("iblock")) {
    $dbRez = CIBlockSection::GetList(
        [],
        [
            'IBLOCK_ID' => 1, '=ID' => $arResult['ID']
        ],
        false,
        ['ID', 'UF_SECTION_META_KEYWORDS', 'UF_SECTION_META_DESCRIPTION']
    );

    while ($arRez = $dbRez->fetch()) {
        $item = $arRez;
    }

    $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues(1, $arResult['ID']);
    $arSEO = $ipropSectionValues->getValues();

    global $APPLICATION;
    $APPLICATION->SetPageProperty('keywords', $arSEO['SECTION_META_KEYWORDS']);
    $APPLICATION->SetPageProperty('description', $arSEO['SECTION_META_DESCRIPTION']);
}


