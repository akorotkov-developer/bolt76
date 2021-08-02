<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
CModule::IncludeModule("iblock");
?>
<div class="catalog_element">
    <form action="/cart/done/" method="post" class="order_form">
<?
    $price = 0;
    $count =0;

    $arSelect = Array("ID", "NAME", "PROPERTY_PRICE", "IBLOCK_ID");
    $arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "ID"=>array_keys($_POST["ITEM"]));
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNext()) {
        $count += $_POST["ITEM"][$ob["ID"]];
        $price += $_POST["ITEM"][$ob["ID"]]*$ob["PROPERTY_PRICE_VALUE"];
        ?><input type="hidden" name="ITEM[<?=$ob["ID"]?>]" value="<?=$_POST["ITEM"][$ob["ID"]]?>"><?
    }
?>
        <div class="alert">Ваш заказ: <?=$count?> товаров на <?=$price?> руб.</div>

        <table class="order_form">
            <tr>
                <td class="name">Ваше имя: </td>
                <td class="value"><input type="text" name="fio"></td>
            </tr>
            <tr>
                <td class="name">Ваш телефон или email: </td>
                <td class="value"><input type="text" name="contact"></td>
            </tr>
        </table>


    </form>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>