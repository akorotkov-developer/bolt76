<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Успешная регистрация");
?>

<div id="content" class="col-sm-12 col-sm-9">
    <h1 class="page-title">Вы успешно зарегистрированы</h1>
    <p>На e-mail, указанный при регистрации, выслано письмо с кодом подтверждения</p>

    <div class="control_buttons">
        <a href="/products/" class="btn btn-default btn-md">
            В магазин
        </a>
        <a href="/personal/cart/" class="btn btn-default btn-md">
            В корзину
        </a>
    </div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>