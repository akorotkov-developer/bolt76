<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<h2>Ваш заказ сформирован под номером <?= $_GET['ORDER_ID'];?></h2>
<p>Наши менеджеры свяжутся с вами в ближайшее время <a href="/catalog/">Продолжить покупки</a></p>