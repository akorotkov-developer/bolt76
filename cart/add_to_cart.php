<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
$cartInfo = Array();
if (isset($_COOKIE["cart"])) {
	$cartInfo = json_decode($_COOKIE["cart"], true);
}

if ($_REQUEST['articul']) {
	$arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRICE");
	$arFilter = Array("IBLOCK_ID" => 1, "PROPERTY_ARTICUL" => trim($_REQUEST['articul']));
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while ($ob = $res->GetNext()) {
		$_POST["ITEM"][$ob['ID']] = 1;
	}
}

foreach ($_POST["ITEM"] as $id => $count) {
	$count = str_replace(",", ".", $count);
	if ((float)$count > 0) {
		$cartInfo["ELEMENTS"][$id] += $count;
	}
}

$price = 0;
$count = 0;

if (sizeof($cartInfo["ELEMENTS"]) > 0) {
	$arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRICE");
	$arFilter = Array("IBLOCK_ID" => 1, "ID" => array_keys($cartInfo["ELEMENTS"]));
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while ($ob = $res->GetNext()) {
		$price += $ob["PROPERTY_PRICE_VALUE"] * (float)$cartInfo["ELEMENTS"][$ob["ID"]];
		$count++;//= $cartInfo["ELEMENTS"][$ob["ID"]];
	}
	$cartInfo["PRICE"] = $price;
	$cartInfo["COUNT"] = $count;

	setcookie("cart", json_encode($cartInfo), strtotime("+1 month"), "/");
	if ($_REQUEST['articul']) {
		LocalRedirect("/cart/");
	}
	echo $count . ' ' . sklon($count, Array("товар", "товара", "товаров")) . ' на сумму<br> ' . $price . ' руб.';
} else {
	setcookie("cart", "", time() - 10, "/");
	echo 'Сейчас пуста';
}
?>