<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

if ($_REQUEST['orderId'] != '') {
    $_REQUEST['orderId'] = 288;

    \Bitrix\Main\Loader::includeModule('sale');

    // Получем все ссылки на файлы для полученных заказов
    $dbRes = \Bitrix\Sale\Order::getList([
        'filter' => [
            'PAY_SYSTEM_ID' => 2, //по платежной системе*/
            'ID' => $_REQUEST['orderId']
        ],
        'order' => ['ID' => 'DESC']
    ]);

    $isSberPay = false;
    if ($order = $dbRes->fetch()){
        $arFields['ORDER_ACCOUNT_NUMBER_ENCODE'] = $order['ACCOUNT_NUMBER'];
        $arFields['ORDER_ID'] = $order['ID'];
        $arFields['ORDER_DATE'] = $order['DATE_INSERT']->format("d.m.Y H:i:s");
        $arFields['ORDER_USER'] = $order['USER_ID'];
        $arFields['PRICE'] = round($order['PRICE'], 2) . ' &#8381;';
        $arFields['BCC'] = 'strprofi@yandex.ru';
        $arFields['SALE_EMAIL'] = 'strprofi@yandex.ru';
        $arFields['PRICE_DELIVERY'] = (float)$order['PRICE_DELIVERY'];

        if ($order['PAY_SYSTEM_ID'] == 2) {
            $isSberPay = true;
        }
    }

    // Получим ФИО пользователя
    $rsUser = CUser::GetByID($arFields['ORDER_USER']);
    $arUser = $rsUser->Fetch();
    $arFields['ORDER_USER'] = $arUser['SECOND_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];

    // Получаем email заказа
    $order = \Bitrix\Sale\Order::load($_REQUEST['orderId']);
    $userEmail = '';

    // свойства заказа
    $propertyCollection = $order->getPropertyCollection();

    // getUserEmail - находит свойство у которого стоит флаг IS_EMAIL
    if ($propUserEmail = $propertyCollection->getUserEmail()) {
        $userEmail = $propUserEmail->getValue();
    } else {

        // поиск свойства путём перебора
        foreach($propertyCollection as $orderProperty) {

            // находим значение по символьному коду
            if ($orderProperty->getField('CODE') == 'EMAIL') {
                $userEmail = $orderProperty->getValue();
                break;
            }
        }
    }

    $arFields['EMAIL'] = $userEmail;


    // Получаем состав заказа
    $dbBasketItems = CSaleBasket::GetList(
        array(
            "NAME" => "ASC",
            "ID" => "ASC"
        ),
        array(
            "LID" => SITE_ID,
            "ORDER_ID" => $_REQUEST['orderId']
        ),
        false,
        false,
        array("ID", "CALLBACK_FUNC", "MODULE",
            "PRODUCT_ID", "QUANTITY", "DELAY",
            "CAN_BUY", "PRICE", "WEIGHT")
    );
    while ($arItems = $dbBasketItems->Fetch())
    {
        $arBasketItems[$arItems['PRODUCT_ID']] = $arItems;
    }

    $arProducts = array_keys($arBasketItems);

    $dbResult = CIBlockElement::GetList(
        [],
        [
            'IBLOCK_ID' => 1,
            'ID' => $arProducts
        ],
        false,
        false,
        ['ID', 'NAME', 'PROPERTY_ARTICUL']
    );

    $arProductsParams = [];
    $arOrderList = [];
    while($arResult = $dbResult->Fetch()){
        $arOrderList[] = $arResult['NAME'] . ' [Артикул: ' . $arResult['PROPERTY_ARTICUL_VALUE'] . '] - ' .
            $arBasketItems[$arResult['ID']]['QUANTITY'] . ' шт. х ' . round($arBasketItems[$arResult['ID']]['PRICE'], 2) . ' &#8381;';
    }

    $arOrderList = implode('</br>', $arOrderList);

    $arFields['ORDER_LIST'] = $arOrderList;

    if ($isSberPay) {
        $arFields['LINK_TO_SBER_PAY'] = 'Ваша ссылка на оплату онлайн <a href="https://strprofi.ru/sber_pay.php?order_id=' . $arFields['ORDER_ID'] . '">https://strprofi.ru/sber_pay.php?order_id=' . $arFields['ORDER_ID'] . '</a>';
    }

    // Отправка сообщения польователю
    \CEvent::Send(
        'SALE_NEW_ORDER',
        's1',
        $arFields
    );
}