<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("Оплата заказа");
?>

<?php
// ?ORDER_ID=11111&PAYMENT_ID=2
use Bitrix\Main\Application;

global $USER;
if (!$USER->IsAuthorized()) {
    $request = Application::getInstance()->getContext()->getRequest();
    $orderId = $request->get('ORDER_ID');
    if (!empty($orderId)) {
        $_SESSION['SALE_ORDER_ID'] = [$orderId];
    }
};

$APPLICATION->IncludeComponent(
    "bitrix:sale.order.payment",
    "",
    array()
); ?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");