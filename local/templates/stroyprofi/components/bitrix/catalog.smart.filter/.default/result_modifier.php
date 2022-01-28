<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($arParams["TEMPLATE_THEME"]) && !empty($arParams["TEMPLATE_THEME"]))
{
	$arAvailableThemes = array();
	$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
	if (is_dir($dir) && $directory = opendir($dir))
	{
		while (($file = readdir($directory)) !== false)
		{
			if ($file != "." && $file != ".." && is_dir($dir.$file))
				$arAvailableThemes[] = $file;
		}
		closedir($directory);
	}

	if ($arParams["TEMPLATE_THEME"] == "site")
	{
		$solution = COption::GetOptionString("main", "wizard_solution", "", SITE_ID);
		if ($solution == "eshop")
		{
			$templateId = COption::GetOptionString("main", "wizard_template_id", "eshop_bootstrap", SITE_ID);
			$templateId = (preg_match("/^eshop_adapt/", $templateId)) ? "eshop_adapt" : $templateId;
			$theme = COption::GetOptionString("main", "wizard_".$templateId."_theme_id", "blue", SITE_ID);
			$arParams["TEMPLATE_THEME"] = (in_array($theme, $arAvailableThemes)) ? $theme : "blue";
		}
	}
	else
	{
		$arParams["TEMPLATE_THEME"] = (in_array($arParams["TEMPLATE_THEME"], $arAvailableThemes)) ? $arParams["TEMPLATE_THEME"] : "blue";
	}
}
else
{
	$arParams["TEMPLATE_THEME"] = "blue";
}

$arParams["FILTER_VIEW_MODE"] = (isset($arParams["FILTER_VIEW_MODE"]) && toUpper($arParams["FILTER_VIEW_MODE"]) == "HORIZONTAL") ? "HORIZONTAL" : "VERTICAL";
$arParams["POPUP_POSITION"] = (isset($arParams["POPUP_POSITION"]) && in_array($arParams["POPUP_POSITION"], array("left", "right"))) ? $arParams["POPUP_POSITION"] : "left";

// TODO попытка опрделить названия для полей фильтров
// Не оптимальное решение, могут возникнуть конфликты названий, будем брать первое попавшееся, но лучше все это переделать

// Получим элементы из текущего раздела
$dbResult = \CIBlockElement::GetList(
    [],
    [
        'IBLOCK_ID' => '1',
        'SECTION_ID' => $arParams['SECTION_ID'],
        'INCLUDE_SUBSECTIONS' => 'Y',
        [
            "LOGIC" => "OR",
            "!PROPERTY_NAME_PARAM_1" => false,
            "!PROPERTY_NAME_PARAM_2" => false,
            "!PROPERTY_NAME_PARAM_3" => false,
            "!PROPERTY_NAME_PARAM_4" => false,
            "!PROPERTY_NAME_PARAM_5" => false,
            "!PROPERTY_NAME_PARAM_6" => false,
            "!PROPERTY_NAME_PARAM_7" => false,
            "!PROPERTY_NAME_PARAM_8" => false,
            "!PROPERTY_NAME_PARAM_9" => false,
            "!PROPERTY_NAME_PARAM_10" => false,
        ],
    ],
    false, false,
    [
        'ID', 'NAME', 'PROPERTY_NAME_PARAM_1', 'PROPERTY_NAME_PARAM_2', 'PROPERTY_NAME_PARAM_3', 'PROPERTY_NAME_PARAM_4',
        'PROPERTY_NAME_PARAM_5', 'PROPERTY_NAME_PARAM_6', 'PROPERTY_NAME_PARAM_7', 'PROPERTY_NAME_PARAM_8', 'PROPERTY_NAME_PARAM_9',
        'PROPERTY_NAME_PARAM_10'
    ]
);

// Составим мапинг названий полей динамических характеристик, используем первое попавшееся название, затем все игнорируем
$arProps = ['PROPERTY_NAME_PARAM_1_VALUE', 'PROPERTY_NAME_PARAM_2_VALUE', 'PROPERTY_NAME_PARAM_3_VALUE', 'PROPERTY_NAME_PARAM_4_VALUE',
    'PROPERTY_NAME_PARAM_5_VALUE', 'PROPERTY_NAME_PARAM_6_VALUE', 'PROPERTY_NAME_PARAM_7_VALUE', 'PROPERTY_NAME_PARAM_8_VALUE',
    'PROPERTY_NAME_PARAM_9_VALUE', 'PROPERTY_NAME_PARAM_10_VALUE'];
$arItems = [];
$arDynamicPropertiesMap = [];
while ($arRes = $dbResult->Fetch()) {
    foreach ($arRes as $key => $value) {
        if (in_array($key, $arProps) && empty($arDynamicPropertiesMap[$key]) && $value != '') {
            $arDynamicPropertiesMap[$key] = $value;
        }
    }
}

$arResult['DYNAMIC_PROPERTIES_MAP'] = $arDynamicPropertiesMap;

if (!function_exists('getParamTitle')) {
    function getParamTitle($sParamName, $arDynamicPropertiesMap) {
        $iParamNumber = str_replace('VALUE_PARAM_', '', $sParamName);

        return $arDynamicPropertiesMap['PROPERTY_NAME_PARAM_' . $iParamNumber . '_VALUE'];
    }
}

$arResult['ARR_DYNAMIC_PROPERTIES_NAMES'] = ['VALUE_PARAM_1', 'VALUE_PARAM_2', 'VALUE_PARAM_3', 'VALUE_PARAM_4', 'VALUE_PARAM_5', 'VALUE_PARAM_6',
    'VALUE_PARAM_7', 'VALUE_PARAM_8', 'VALUE_PARAM_9', 'VALUE_PARAM_10'];

/** Правильная сортировка цифр в фильтре */
/* Sort values */

if ($_GET['tst'] == 'tst') {

    $properties = CIBlockProperty::GetList(
        [],
        [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => 1
        ]
    );

    while ($prop_fields = $properties->GetNext())
    {
        echo $prop_fields["ID"]." - ".$prop_fields["NAME"]."<br>";
    }


    foreach($arResult["ITEMS"] as $key => $arItem)
    {

    }

    foreach ($arResult["ITEMS"] as $key => $arValue) {
        if (!(isset($arValue["PRICE"])) && count($arValue["VALUES"])) {
            $arValues = array();                  // For new values
            $arResValues = array();                  // For new values in arResult
            $bSort = true;                     // Sort or not sort
            $iValuesCount = count($arValue["VALUES"]);   // Num of elements
            $sUnit = false;                  // Last of unit

            foreach ($arValue["VALUES"] as $keyVal => $value) {
                $arMatches = array();
                preg_match("/([\d\,\.]+)\s*([А-яA-z\d\-\_]*)/", $value["VALUE"], $arMatches);

                if ($arMatches[1] && $arMatches[2]) {
                    if ($sUnit && $sUnit <> $arMatches[2]) {
                        $bSort = false;
                        break;
                    } else {
                        $sUnit = $arMatches[2];
                    }
                } else {
                    $bSort = false;
                    break;
                }

                if ($bSort) {
                    $arMatches["key"] = $keyVal;
                    $arValues[] = $arMatches;
                }
            }

            if ($bSort) {
                usort($arValues, "compare");

                foreach ($arValues as $arVal) {
                    $arResValues[$arVal["key"]] = $arValue["VALUES"][$arVal["key"]];
                }

                $arResult["ITEMS"][$key]["VALUES"] = $arResValues;
            }
        }
    }

        function compare($v1, $v2)
        {
            if ($v1[1] == $v2[1]) return 0;
            return ($v1[1] < $v2[1]) ? -1 : 1;
        }
}
