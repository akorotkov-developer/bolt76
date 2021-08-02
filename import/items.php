<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

set_time_limit(0);

global $iblock;
$iblock =1;

$strCatID = 332722;
$siteCatID= 591;


function addUpdateElement($item, $siteCatID){
    global $iblock;
    $el = new CIBlockElement;
    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_PHOTO_ID");
    $arFilter = Array("IBLOCK_ID" => $iblock, "PROPERTY_ROW_ID"=>$item->ID, "SECTION_ID"=>$siteCatID);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    if ($ob = $res->GetNext()) {

        $propsToUpdate = Array(
            "PRICE"=>$item->CZena1,
            "PRICE_OPT"=>$item->CZena2,
            "PRICE_OPT2"=>$item->CZena3
        );

        if($ob["PROPERTY_PHOTO_ID_VALUE"]!=$item->Foto){
            $propsToUpdate["PHOTO_ID"]=$item->Foto;
            $pic=CFile::MakeFileArray("http://strprofi.ru/export/photos.php?id=".$item->Foto);
            if($pic["size"]>0){
                $el->Update($ob["ID"], Array("PREVIEW_PICTURE"=>$pic));
            }
        }

        CIBlockElement::SetPropertyValuesEx($ob["ID"], $iblock, $propsToUpdate);

        echo 'UPD:'.$ob["ID"];

    }else{


       // print '<pre>' . print_r($item, true) . '</pre>';
       // die();

        $pic = CFile::MakeFileArray("http://strprofi.ru/export/photos.php?id=".$item->Foto);

        $arLoad =Array(
            "IBLOCK_ID"=>$iblock,
            "IBLOCK_SECTION_ID"=>$siteCatID,
            "NAME"=>$item->Svertka,
            "PREVIEW_TEXT"=>$item->Opisanie,
            "PROPERTY_VALUES"=>array(
                "ARTICUL"=>$item->Artikul,
                "ROW_ID"=>$item->ID,
                "PRICE"=>$item->CZena1,
                "PRICE_OPT"=>$item->CZena2,
                "PRICE_OPT2"=>$item->CZena3,
                "PHOTO_ID"=>$item->Foto,
                "UNITS"=>$item->EdIzmereniya
            ),
        );

        if($pic["size"]>0){
            $arLoad["PREVIEW_PICTURE"]=$pic;
        }

        $ID=$el->Add($arLoad);

        echo $el->LAST_ERROR.'<br>';
        echo $ID.'<br>';
    }

}


function parseSection($strCatID, $siteCatID){
    $xml = simplexml_load_file("http://strprofi.ru/export/items.php?cat=".$strCatID);


    foreach($xml->item as $item){
       addUpdateElement($item, $siteCatID);
    }

}



parseSection($strCatID, $siteCatID);