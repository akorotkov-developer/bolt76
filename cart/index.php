<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
CModule::IncludeModule("iblock");
?>
<?$APPLICATION->IncludeComponent("prominado:main.include", ".default", array(
	"AREA_FILE_SHOW" => "file",
	"PATH" => "form.php",
	"EDIT_TEMPLATE" => "",
	"THANKS" => "Благодарим вас за заказ!<br/>В ближайшее время мы свяжемся с вами.",
	"EMPTY" => "Ваша корзина сейчас пуста.<br/> Перейти в <a href='/catalog/'>каталог</a>"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>