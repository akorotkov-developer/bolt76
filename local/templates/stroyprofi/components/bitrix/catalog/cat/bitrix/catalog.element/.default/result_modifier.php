<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();


// Проверяем, не является ли раздел распродажей
$dbResult = CIBlockSection::GetList(
    [],
    [
        'IBLOCK_ID' => 1,
        'CODE' => 'rasprodazha',
    ],
    false,
    [
        'ID', 'CODE'
    ]
);
$saleSectionId = null;
if ($result = $dbResult->fetch()) {
    $saleSectionId = $result['ID'];
}

$dbGroups = CIBlockElement::GetElementGroups($arResult['ID'], true);
$curSectionId = [];
if($arGroup = $dbGroups->Fetch()) {
    if ($arGroup['ID'] != $saleSectionId) {
        $curSectionId = $arGroup['ID'];
    }
}

/*$arFields = [
    'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    'ID' => $arResult['ORIGINAL_PARAMETERS']['SECTION_ID']
];*/
$arFields = [
    'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    'ID' => $curSectionId
];
$obSections = CIBlockSection::GetList(
    ['SORT' => 'ASC'],
    $arFields,
    false,
    ['ID', 'PICTURE', 'DESCRIPTION']
);

$arSection = [];
if ($arRez = $obSections->Fetch()) {
    $arSection = $arRez;
}

$arResult['SECTION_DESCRIPTION'] = $arSection['DESCRIPTION'];

if (!empty($arResult['PREVIEW_PICTURE']['SRC'])) {
    $arResult['MORE_PHOTO_PICTURE']['ID'] = (int) $arResult['PREVIEW_PICTURE']['ID'];
    $arResult['MORE_PHOTO_PICTURE']['SRC'] =  $arResult['PREVIEW_PICTURE']['SRC'];
    $arResult['MORE_PHOTO_PICTURE']['WIDTH'] = 150;
    $arResult['MORE_PHOTO_PICTURE']['HEIGHT'] = 150;
} else if ($arSection['PICTURE']) {
    $arSection['PICTURE'] = CFile::GetFileArray($arSection['PICTURE']);
    $arResult['MORE_PHOTO_PICTURE']['ID'] = (int)$arSection['PICTURE']['ID'];
    $arResult['MORE_PHOTO_PICTURE']['SRC'] = $arSection['PICTURE']['SRC'];
    $arResult['MORE_PHOTO_PICTURE']['WIDTH'] = 150;
    $arResult['MORE_PHOTO_PICTURE']['HEIGHT'] = 150;
}

// Установка заголовка
$arResult['NAME'] = ($arResult['PROPERTIES']['Naimenovanie']['VALUE'] != '') ? $arResult['PROPERTIES']['Naimenovanie']['VALUE'] : $arResult['NAME'];

// Добавим фото если есть дополнительные фото в товаре
if (count($arResult['PROPERTIES']['PHOTOS']['VALUE']) > 0 && $arResult['PROPERTIES']['PHOTOS']['VALUE'] !== false) {
    $arPhotos = [];
    if (!empty($arResult['PREVIEW_PICTURE']['SRC'])) {
        $arPhotos[] = $arResult['PREVIEW_PICTURE']['SRC'];
    }

    foreach ($arResult['PROPERTIES']['PHOTOS']['VALUE'] as $photoId) {
        $arPhotos[] = \CFile::GetFileArray($photoId)['SRC'];
    }
}

$arResult['PHOTOS'] = $arPhotos;

/**
 * Получаем обычную и оптовую цену товара
 */
$allProductPrices = \Bitrix\Catalog\PriceTable::getList([
    "select" => ["*"],
    "filter" => [
        "=PRODUCT_ID" => $arResult['ID'],
    ],
    "order" => ["CATALOG_GROUP_ID" => "ASC"]
])->fetchAll();

$arResult['ALL_PRODUCT_PRICES'] = $allProductPrices;

/**
 * Сформируем дополнительные вкладки, если они есть
 */
if (!function_exists('translit')) {
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
}

if (!empty($arResult['PROPERTIES']['ADDITIONAL_PRODUCT_INFORMATION']['VALUE']) && $arResult['PROPERTIES']['ADDITIONAL_PRODUCT_INFORMATION']['VALUE'] != '') {
    $arPrepareAdditionalTabs = explode(':', $arResult['PROPERTIES']['ADDITIONAL_PRODUCT_INFORMATION']['VALUE']);

    $arAdditionalTabs = [];
    $iTabNumber = 0;
    foreach ($arPrepareAdditionalTabs as $key => $value) {
        if ($key % 2 == 0) {
            $arAdditionalTabs[$iTabNumber]['NAME'] = $value;
            $arAdditionalTabs[$iTabNumber]['CODE'] = translit($value);
        } else {
            $arAdditionalTabs[$iTabNumber]['FILE'] = $value;
            $iTabNumber++;
        }
    }

    $arResult['ADDITIONAL_TABS'] = $arAdditionalTabs;
}

/** Проерка находится товар в избранном */
global $USER;
if(!$USER->IsAuthorized()) // Для неавторизованного
{
    global $APPLICATION;
    $arFavorites = unserialize($_COOKIE["favorites"]);
} else {
    $idUser = $USER->GetID();
    $rsUser = CUser::GetByID($idUser);
    $arUser = $rsUser->Fetch();
    $arFavorites = $arUser['UF_FAVORITES'];

}

foreach ($arFavorites as $favoriteItemId) {
    if ($arResult['ID'] == $favoriteItemId) {
        $arResult['IS_FAVORITE'] = true;
    }
}

/**
 * Ссылки вперед назад
 */
$dbResult = CIBlockElement::GetList(
    [
        'PROPERTY_Naimenovanie' => 'ASC'
    ],
    [
        'IBLOCK_ID' => 1,
        'ACTIVE' => 'Y',
        'SECTION_ID' => $arResult['ORIGINAL_PARAMETERS']['SECTION_ID']
    ],
    false,
    false,
    ['ID', 'DETAIL_PAGE_URL']
);

$arItems = [];
$dbResult->SetUrlTemplates("#SITE_DIR#/catalog/#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#");
while($arRes = $dbResult->GetNext()) {
    $arItems[] = $arRes;
}

// Назначаем ссылки вперед и назад
foreach ($arItems as $key => $item) {
    if ($item['ID'] == $arResult['ID']) {
        $arResult['PREV_LINK'] = $arItems[$key - 1]['DETAIL_PAGE_URL'];
        $arResult['NEXT_LINK'] = $arItems[$key + 1]['DETAIL_PAGE_URL'];
    }
}

// Ссылка назад к списку
$page = explode('/', $APPLICATION->GetCurPage());
unset($page[0]);
unset($page[count($page)]);
$arResult['RETURN_LINK'] = '/' . implode('/', $page) . '/';
