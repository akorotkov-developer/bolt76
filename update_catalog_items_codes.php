<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Компания СтройПрофи. О компании");
$APPLICATION->SetTitle("СтройПрофи");


use \Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('sale');

function translit($s) {
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'x','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
    $s = str_replace(" ", "_", $s); // заменяем пробелы знаком минус
    return $s; // возвращаем результат
}

//1. Получаем все товары на сайте
$arFilter = [
    'IBLOCK_ID' => 1,
    'INCLUDE_SUBSECTIONS' => 'Y'
];

$dbRez = CIBlockElement::GetList(
    ['SORT'=>'ASC'],
    $arFilter,
    false,
    false,
    ['ID', 'NAME']
);

$arItems = [];
while($arRez = $dbRez->Fetch())
{
    $arItems[] = $arRez;
}

foreach ($arItems as $item) {
    $elem = new CIBlockElement();
    $isUpdated = $elem->Update($item['ID'], ['CODE' => translit($item['NAME'])]);
}
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>