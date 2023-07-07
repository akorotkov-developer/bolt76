<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
$cartInfo = Array();
if(isset($_COOKIE["cart"])){
    $cartInfo = json_decode($_COOKIE["cart"], true);
}


unset($cartInfo["ELEMENTS"][(int)$_POST["id"]]);

$price =0;
$count =0;

if(sizeof($cartInfo["ELEMENTS"])>0){
    $arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRICE");
    $arFilter = Array("IBLOCK_ID" => 1, "ID"=>array_keys($cartInfo["ELEMENTS"]));
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNext()) {
        $price += $ob["PROPERTY_PRICE_VALUE"]*(int)$cartInfo["ELEMENTS"][$ob["ID"]];
        $count += $cartInfo["ELEMENTS"][$ob["ID"]];
    }
    $cartInfo["PRICE"] = $price;
    $cartInfo["COUNT"] = $count;

    setcookie("cart", json_encode($cartInfo),strtotime("+1 month"), "/");

    //echo $count.' товаров на '.$price.' руб.';
}else{
    setcookie("cart", "" ,time()-10, "/");
    //echo 'Сейчас пуста';
}
?>