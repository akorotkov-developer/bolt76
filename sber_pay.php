<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sberbank.ecom2/handler/handler.php");

use Bitrix\Sale;

$orderId = $_GET['order_id'];

if (!empty($orderId)) {
    $order = Sale\Order::load($orderId);
    $paymentCollection = $order->getPaymentCollection();
    foreach ($paymentCollection as $payment) {
        $service = $payment->getPaySystem();
        $paySystem = $payment;
    }

    $obSberBank = new \Sale\Handlers\PaySystem\sberbank_ecom2Handler('CUSTOM', $service);
    $params = $obSberBank->initiatePay($paySystem);
}
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>