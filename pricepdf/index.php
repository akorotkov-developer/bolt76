<?php
$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

require($_SERVER["DOCUMENT_ROOT"] . "/local/include/vendor/autoload.php");

use HeadlessChromium\BrowserFactory;

$browserFactory = new BrowserFactory();

// starts headless Chrome
$browser = $browserFactory->createBrowser();

try {
    // creates a new page and navigate to an URL
    $page = $browser->createPage();
    $page->navigate('https://strprofi.ru/price-print.php')->waitForNavigation('load', 20000);
    // pdf
    $page->pdf(['printBackground' => false])->saveToFile($_SERVER['DOCUMENT_ROOT'] . '/pricepdf/price1.pdf');
} finally {
    // bye
    $browser->close();
}