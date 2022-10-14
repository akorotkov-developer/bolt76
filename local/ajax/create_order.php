<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Sale;

\Bitrix\Main\Loader::includeModule('sale');

/**
 * Создание заказа через API битрикса
 */
// Получаем пользователя киоска
$filter = [
    'LOGIN' => 'kiosk',
];

$rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $filter); // выбираем пользователей
while($rsUsers->NavNext(true, "f_")) {
    $userId = $f_ID;
};

// Получаем текущую корзину пользователя
$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

// Создаем заказ
$order = Bitrix\Sale\Order::create(SITE_ID, $userId);
$order->setPersonTypeId(1);
$order->setBasket($basket);

// Отгрузки (Самовывоз со склада ID = 2)
$shipmentCollection = $order->getShipmentCollection();
$shipment = $shipmentCollection->createItem(
    Bitrix\Sale\Delivery\Services\Manager::getObjectById(2)
);

$shipmentItemCollection = $shipment->getShipmentItemCollection();

foreach ($basket as $basketItem)
{
    $item = $shipmentItemCollection->createItem($basketItem);
    $item->setQuantity($basketItem->getQuantity());
}

// Создаем оплаты
$paymentCollection = $order->getPaymentCollection();
$payment = $paymentCollection->createItem(
    Bitrix\Sale\PaySystem\Manager::getObjectById(3)
);
$payment->setField("SUM", $order->getPrice());
$payment->setField("CURRENCY", $order->getCurrency());

// Создание комментария
$order->setField('USER_DESCRIPTION', 'KIOSK');

// Сохранение заказа
$order->save();


// Создаем XML для СБИС++
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/ordertoxml/OrderXml.php';
$obOrderXml = new OrderXml($order->getId());
$isCreated = $obOrderXml->createXml(true);

if ($isCreated) {
    echo $order->getId();
}

?>