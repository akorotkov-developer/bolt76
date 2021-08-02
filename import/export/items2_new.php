<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="utf-8"?> ';

$Razdel = (int)$_GET['cat'];
if (!$Razdel) die("Не задан раздел");

CModule::IncludeModule("iblock");

//Получим дерево сексций с элементами внутри
$arFilter = [
    'ACTIVE' => 'Y',
    'IBLOCK_ID' => 1,
    'UF_ROWID' => $Razdel
];
$arSelect = ['IBLOCK_ID', 'ID', 'NAME', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID', 'PICTURE', 'UF_ROWID'];
$arOrder = ['DEPTH_LEVEL' => 'ASC', 'SORT' => 'ASC'];
$rsSections = CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);

$iSectionId = false;
if ($arSection = $rsSections->GetNext()) {
    $iSectionId = $arSection['ID'];
}

//Получим все элементы внутри раздела
if (!$iSectionId) {
    die("Не задан раздел");
}

$arFilter = [
    'IBLOCK_ID' => 1,
    'PROPERTY_SHOW_IN_PRICE' => [1],
    'SECTION_ID' => $iSectionId,
    'INCLUDE_SUBSECTIONS' => 'Y'
];
$dbRes = CIBlockElement::GetList(
    ['SORT"=>"ASC'],
    $arFilter,
    false,
    false,
    [
        'ID',
        'NAME',
        'PROPERTY_NOMNOMER',
        'PROPERTY_SHOW_IN_PRICE',
        'IBLOCK_SECTION_ID',
        'PREVIEW_PICTURE',
        'PROPERTY_ARTICUL',
        'PROPERTY_UPAKOVKA',
        'PROPERTY_UNITS',
        'PROPERTY_PRICE',
        'PROPERTY_ROWID',
        'PROPERTY_NOMNOMER',
        'PROPERTY_NomenklaturaGeog',
        'PROPERTY_VES',
        'PROPERTY_UPAKOVKA',
        'PROPERTY_UPAKOVKA2',
        'PROPERTY_NAIMENOVANIE',
        'PREVIEW_PICTURE',
        'PROPERTY_PRICE',
        'PRICE_OPT',
        'PRICE_OPT2',
        'PROPERTY_UNITS'
    ]
);
while($arRez = $dbRes->Fetch())
{
    $arItems[] = $arRez;
}

//Составить XML для выбранных товаров
?>
    <items>
        <?php
        foreach ($arItems as $item) {
            if (!$item['PROPERTY_ARTICUL_VALUE']) {
                continue;
            }?>

            <item>
                <ID><?=$item['PROPERTY_ROWID_VALUE'];?></ID>
                <NomNomer><?=$item['PROPERTY_PROPERTY_NOMNOMER_VALUE'];?></NomNomer>
                <NomenklaturaGeog><?=$item['PROPERTY_NomenklaturaGeog_VALUE'];?></NomenklaturaGeog>
                <Ves><?=$item['PROPERTY_VES_VALUE'];?></Ves>
                <VUpakovke><?=$item['PROPERTY_UPAKOVKA_VALUE'];?></VUpakovke>
                <VUpakovke2><?=$item['PROPERTY_UPAKOVKA_VALUE'];?></VUpakovke2>
                <Ostatok><?=$item['PROPERTY_OSTATOK_VALUE'];?></Ostatok>
                <Artikul><?=$item['PROPERTY_ARTICUL_VALUE'];?></Artikul>
                <Naimenovanie><?=$item['PROPERTY_NAIMENOVANIE_VALUE'];?></Naimenovanie>
                <Foto><?=$item['PREVIEW_PICTURE'];?></Foto>
                <CZena1><?=$item['PROPERTY_PRICE_VALUE'];?></CZena1>
                <CZena2><?=$item['PRICE_OPT_VALUE'];?></CZena2>
                <CZena3><?=$item['PRICE_OPT2_VALUE'];?></CZena3>
                <EdIzmereniya><?=$item['PROPERTY_UNITS_VALUE'];?></EdIzmereniya>
                <?php
                if ($item['Opisanie']) {
                    ?><Opisanie><?=$item['Opisanie'];?></Opisanie>
                <?php }?>
            </item>
        <?php } ?>
    </items>