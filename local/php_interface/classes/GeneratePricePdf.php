<?php
use HeadlessChromium\BrowserFactory;


class GeneratePricePdf
{
    public static function run(): string
    {
        /*$browserFactory = new BrowserFactory();
        // starts headless Chrome
        $browser = $browserFactory->createBrowser();*/

        /*try {
            // creates a new page and navigate to an URL
            $page = $browser->createPage();
            $page->navigate('https://strprofi.ru/price-print.php')->waitForNavigation('load', 20000);
            // pdf
            $page->pdf(['printBackground' => false])->saveToFile($_SERVER['DOCUMENT_ROOT'] . '/pricepdf/price1.pdf');
        } finally {
            // bye
            $browser->close();
        }*/

        return '\\' . __METHOD__ . '();';
    }
}

