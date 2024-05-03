<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

require($_SERVER["DOCUMENT_ROOT"] . "/local/include/vendor/autoload.php");


// Создаем экземпляр TCPDF
$pdf = new TCPDF();

// Добавляем новую страницу
$pdf->AddPage();

// Получаем содержимое вашего php скрипта
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . '/price-print.php'; // Подключаем ваш php скрипт
$content = ob_get_clean();

// Выводим содержимое на PDF файл
$pdf->writeHTML($content, true, false, true, false, '');

// Сохраняем PDF файл
$pdf->Output($_SERVER["DOCUMENT_ROOT"] .'/output.pdf', 'F');