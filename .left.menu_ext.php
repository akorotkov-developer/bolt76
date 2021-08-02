<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$aMenuLinksExt=$APPLICATION->IncludeComponent("profi:menu.sections", "", array(
        //  "IBLOCK_TYPE_ID" => "shop",
        "IBLOCK_ID"=>1,
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600",
        "MAX_LEVEL"=>3,
        "DEPTH_LEVEL"=>3
    ),
    false,
    Array('HIDE_ICONS' => 'Y')
);
$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>