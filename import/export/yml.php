<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="utf-8"?> ';
?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="<?=date("Y-m-d H:i");?>">
    <shop>
        <name>СтройПрофи</name>
        <company>СтройПрофи</company>
        <url>http://strprofi.ru/</url>
        <currencies>
            <currency id="RUR" rate="1" plus="0"/>
        </currencies>
        <categories>
        <?
        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "PICTURE", "UF_TEMPLATE");
        $arFilter = Array("IBLOCK_ID"=>"1", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "INCLUDE_SUBSECTIONS" => "Y");
        $res = CIBlockSection::GetList(Array("left_margin" => "asc"), $arFilter, false, $arSelect);
        $sections = Array();
        while($ob = $res->GetNext()) {
            if ($ob['UF_TEMPLATE'] == 1) {
                $sections[$ob['ID']] = $ob;
            }
            ?><category id="<?=$ob['ID'];?>"<?if($ob['IBLOCK_SECTION_ID']){?> parentId="<?=$ob['IBLOCK_SECTION_ID'];?>"<?}?>><?=trim($ob['NAME']);?></category><?
        }
        ?>
        </categories>
        <offers>
            <?
            $arSelect = Array("ID", "NAME", "IBLOCK_SECTION_ID", "PREVIEW_TEXT", "PREVIEW_PICTURE", "PROPERTY_OSTATOK", "DETAIL_PAGE_URL", "DATE_ACTIVE_FROM", "PROPERTY_NAIMENOVANIE", "PROPERTY_PRICE", "PROPERTY_PRICE_OPT", "PROPERTY_UNITS");
            $arFilter = Array("IBLOCK_ID"=>"1", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false/*Array("nPageSize"=>10)*/, $arSelect);
            while($ob = $res->GetNext())
            {
                if (!$ob['PROPERTY_PRICE_VALUE']) continue;


                $ob["NAME"] = $ob["PROPERTY_NAIMENOVANIE_VALUE"]?$ob["PROPERTY_NAIMENOVANIE_VALUE"]:$ob["NAME"];//($ob["NAME"]=="-"?$ob["PROPERTY_NAIMENOVANIE_VALUE"]:$ob["NAME"]);
//                $ob["NAME"] = ($ob["NAME"]=="-"?$ob["PROPERTY_NAIMENOVANIE_VALUE"]:$ob["NAME"]);
                $file = Array();
                if ($sections[$ob['IBLOCK_SECTION_ID']]["PICTURE"]) {
                    $file = CFile::ResizeImageGet($sections[$ob['IBLOCK_SECTION_ID']]["PICTURE"], array('width' => 150, 'height' => 150), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                } elseif ($ob["PREVIEW_PICTURE"]) {
                    $file = CFile::ResizeImageGet($ob["PREVIEW_PICTURE"], array('width' => 150, 'height' => 150), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                }
                ?>
                <offer id="<?=$ob['ID'];?>" available="<?=$ob['PROPERTY_OSTATOK_VALUE']?"true":"false";?>">
                    <name><?=$ob["NAME"];?></name>
                    <url>http://strprofi.ru<?=$ob['DETAIL_PAGE_URL'];?></url>
                    <price><?=$ob['PROPERTY_PRICE_VALUE'];?></price>
                    <currencyId>RUR</currencyId>
                    <categoryId><?=$ob['IBLOCK_SECTION_ID'];?></categoryId>
                    <?if($file['src']){?>
                    <picture>http://strprofi.ru<?=$file['src'];?></picture>
                    <?}?>
                    <?/*<available><?=$ob['PROPERTY_OSTATOK_VALUE']?"Склад":"Заказ";?></available>*/?>
                    <?/*<delivery>false</delivery>*/?>
                    <?
                    $ob['PREVIEW_TEXT'] = str_replace("&nbsp;", " ", $ob['PREVIEW_TEXT']);
                    $ob['PREVIEW_TEXT'] = strip_tags($ob['PREVIEW_TEXT']);
                    ?>


                    <description><?=$ob['PREVIEW_TEXT'];?></description>
                </offer>
            <?
            }
            ?>
        </offers>
    </shop>
</yml_catalog>