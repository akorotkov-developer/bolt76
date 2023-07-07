<?



function compare($x, $y)
{
    $x = (array)$x;
    $y = (array)$y;
    //msg($x);
    //msg($y);
    //exit;
    //$a = preg_replace('(\.|x|х)', '', trim($x['Svertka']));
    //$b = preg_replace('(\.|x|х)', '', trim($y['Svertka']));
    $a = $x['Naimenovanie'];
    $b = $y['Naimenovanie'];
    //msg($a);
    //msg($b);
    //exit;
    if ($a == $b)
        return 0;
    else if ($a < $b)
        return -1;
    else
        return 1;
}

$sizes = array(
    'h3' => 5,
    'h3Margin' => 5,
    'h2' => 10,
    'h2Margin' => 5,
    'sectionDivider' => 5,
    'elementRow' => 3,
    'elementPaddingTop' => 1,
    'elementPaddingBottom' => 1,
    'elementBorderBottom' => 0.5,
    'pageHeight' => 270,
    //'forceBreak' => 275,
    'forceBreak'=>273.5,
    'forceBreakSmall'=>278,
    'pageHeaderHeight' => 15,
    'pageHeaderMarginBottom' => 5,
    'h3Break' => 40,
    'h2Break' => 30,

);

/*
$addressHTML = 'г. Ярославль, Ленинградский пр-т, д.33<br>
        офис: 501 5-й этаж<br>
        магазин-склад: модуль 109 1-й этаж';
*/

$addressHTML = '<table><tr><td><img alt="" class="logo" src="/img/logo.png" /></td><td>strprofi.ru<br/>(4852) 58-04-45</td></tr></table>';

?>

<!doctype html>
<html>
<head>
<title>Прайс-лист</title>
<style type="text/css" media="all">

html, body {
    padding: 0;
    margin: 0;

}

.pageBox {
    position: relative;
    height: <?=$sizes['pageHeight']?>mm;
    page-break-after: always;
    width: 190mm;
    margin: auto;

}

#contents, #first-list{
    width: 190mm;
    margin: auto;
}

.pageBox div {
    /*display: none;*/
}

.pageBox div.clear {
    display: block;
}

.pageBox div.page-footer {
    height: 5mm;
    line-height: 5mm;
    overflow: hidden;
    display: block;
    position: absolute;
    bottom: 0;
    text-align: right;
    width: 100%;


}

.contentsTitle {
    font-size: 17pt;
    text-align: center;
}

#contents {
    page-break-after: always;
}

#contents .h1 {
    font-size: 10pt;
}

#contents .h2 {
    font-size: 10pt;

}

#contents .h3 {
    font-size: 10pt;

}

#contents .h2 .title div {
    margin-left: 1cm;
}

#contents .h3 .title div {
    margin-left: 2cm;
}

#contents tr {
    border-bottom: 0.5mm dotted #000;
}

#contents td.title {
    width: 50%;

}

tr.empty td {
    border-bottom-color: #fff;
    border-right-color: #fff;
}

tr.empty .first-td{
    border-left-color: #fff;
}

#contents td.bordered {

}

#contents td.page {
    width: 3%;
    text-align: left;
}

.address table td {
    height: 15mm;
    vertical-align: middle;
    padding-right: 5mm;
    font-size: 12pt;
    line-height: 1.4em;
}

.address .logo {
    position: relative;
    top: 1mm;
}

body {
    font-family: Arial, sans-serif;
    font-size: 7pt;

}

.titleName {
    font-size: 20pt;
    text-align: center;
    margin-bottom: 50mm;
}

.logoBox {
    padding-top: 40mm;
    margin-bottom: 40mm;
}

#first-list {
    page-break-after: always;
    font-size: 13pt;
}

.h2-box {
    position: relative;
    padding: 0 2.5mm;

}

.h2-box h2 {
    position: relative;
    z-index: 5;
}

.h2-box .h2-background {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.address {
    float: right;
    color: #fff;
    font-size: 9pt;
    line-height: 12pt;
    height: 15mm;
    overflow: hidden;
}

h1 {
    font-size: 14pt;
    font-weight: bold;
    display: table-cell;
    height: <?=$sizes['pageHeaderHeight']?>mm;
    vertical-align: middle;

    color: #fff;
    padding: 0 5mm;
}

.header {
    /*background: #FF7920;*/
    /*padding: 5mm;*/
    color: #fff;
    margin: 0 0 <?=$sizes['pageHeaderMarginBottom']?>mm 0;
    height: <?=$sizes['pageHeaderHeight']?>mm;
    overflow: hidden;
    position: relative;
}

.header div, .header h1 {
    position: relative;
    z-index: 5;
    color: #000;
}

img.header-background {
    position: absolute;
    display: block;
    width: 100%;
    height: 100%;
    z-index: 1;
    left: 0;
    top: 0;
}

.page-break-after {
    /*page-break-after: always;*/
    font-size: 0;
    height: 0;
    display: none;

}

.page-break-before {
    page-break-before: always;
}

h3 {
    padding: 0;
    /*font-weight: normal;*/
    line-height: <?=$sizes['h3']?>mm;

    font-size: 11pt;
    margin: 0 0 <?=$sizes['h3Margin']?>mm 0;
    /*background: gray;*/
}

h2 {
    padding: 0;
    margin: 0;
    /*font-weight: normal;*/
    font-size: 16pt;
    line-height: <?=$sizes['h2']?>mm;

    margin-bottom: <?=$sizes['h2Margin']?>mm;
    text-align: left;
    overflow: hidden;
}

.sectionDivider {
    height: <?=$sizes['sectionDivider']?>mm;
    /*background: green;*/
}

.column {
    float: left;
    border-right: 0.5mm solid #ccc;
}

.clear {
    float: none;
    clear: both;
    font-size: 0;
    height: 0;
}

table {
    /*background: #000;*/
    border-collapse: collapse;
    empty-cells: show;
    border-spacing: 0;
    width: 100%;
    border: 0 none;
}

.nowrap {
    white-space: nowrap;
}

td {
    padding: 0;
}

td.content {
    padding: 1mm;
    border-bottom: <?=$sizes['elementBorderBottom']?>mm solid #ccc;
    border-right: 0.5mm solid #ccc;
}

.td-content {
    /*height: <?=($sizes['elementRow'] - 0.5)?>mm;*/
    line-height: <?=($sizes['elementRow'])?>mm;
}

.first-tr td {
    border-top: 0.5mm solid #ccc;
    /*border-bottom: 0.5mm solid #ccc;*/
    font-weight: bold;
    padding: 0;
}

.first-tr .td-content {
    position: relative;
    padding: <?=$sizes['elementPaddingTop']?>mm;
    line-height: <?=$sizes['elementRow']?>mm;
}

.first-tr .td-content div {
    position: relative;
    z-index: 5;
}

.first-tr .th-background {
    position: absolute;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
}

.first-td {
    border-left: 0.5mm solid #ccc;
}

.section {
    position: relative;
    padding-left: 24mm;
}

.section.no-padding {
    padding: 0;
}

.section-image-box {
    position: absolute;
    left: 0;
    top: 5mm;
    width: 20mm;
    text-align: center;
}

.section-image-box img {
    max-width: 100%;

}

.sectionsColumn {
    float: left;
    width: 50%;
    /*margin-left: 50%;*/
    /*height: <?=$sizes['pageHeight']?>mm;*/
    /*border-bottom: solid black 1mm;*/
    position: relative;
}

.firstColumn {
    width: 50%;
    float: left;
    /*height: <?=$sizes['pageHeight']?>mm;*/
    position: relative;
}

.firstColumn .section,
.firstColumn .h2-box,
.firstColumn .h3-box {
    margin-right: 2.5mm;
}

.sectionsColumn .section,
.sectionsColumn .h2-box,
.sectionsColumn .h3-box {
    margin-left: 2.5mm;
}

.section-image-td {
    text-align: center;
    vertical-align: top;
    width: 1%;
}

td.element-image {
    width: 1%;
    text-align: center;
    vertical-align: middle;
    height: 20mm;
}

.element-image-box {
    width: 22mm;
}

.element-image-box img {
    max-height: 18mm;
    max-width: 20mm;
}

.section-image-td div {
    width: 20mm;
}

.section-image-td div img {
    max-width: 20mm;
}

td.elementName {
    width: 69%;
}

td.elementPrice,
td.elementOpt,
td.elementCode,
td.elementPack {
    width: 10%;
}

td.elementOpt {
    text-align: right;
}

td.elementEd {
    width: 15%;
}

    /*
.td-content .pos {
   position: absolute;
   height: <?=$sizes['elementRow']?>mm;
    width: 100%;
    overflow: hidden;
}
*/

.td-content {
    position: relative;
}

</style>

</head>
<body>
<!--
<div style="font-size: 0; width: 100%; height: 100%;"><img src="/img/eee.gif" alt="" style="width: 100%; height: 100%; display: block;" /></div>
</body>
</html>
-->



<?

//exit;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");


class getArrayFromXML
{

    private $collection = array();

    public function getCollection()
    {
        return $this->collection;
    }

    public function getElementsArray($xmlObject, $sectionID)
    {
        $a = (array)$xmlObject;
        //msg($a); exit;
        $result = array();
        //msg($a['item']);
        //msg($a['item']);
        //exit;
        if (!is_array($a['item'])) {
            //msg('!is_array($a["item][0])');
            //exit;
            //msg($a); exit;
            $u = (array)$a['item'];
            unset($a['item']);
            $a['item'] = array();
            $a['item'][0] = $u;
        }
        //msg($a['item']);
        //exit;
        $ar = (array)$a['item'];


        //msg($ar); exit;


        usort($ar, 'compare');
        //msg($ar); exit;


        //if ($sectionID==18634) {
        //msg($ar);
        //exit;
        //}


        foreach ($ar as $ao) {
            if (!empty($ao)) {
                $ao = (array)$ao;

                $getElementInfo = CIBlockElement::GetList(Array(), array("PROPERTY_ROWID" => (int)$ao['ID'], "IBLOCK_ID" => 1), false, false, array("ID", "NAME", "PREVIEW_PICTURE"));
                $count = $getElementInfo->SelectedRowsCount(); // количество полученных записей из таблицы
                $elementInfo = $getElementInfo->GetNext();

                //msg($elementInfo); exit;


                //msg($ao); exit;

                $unit = trim((string)$ao['EdIzmereniya']);


                $na = array();
                $svertka = trim((string)$ao['Svertka']);

                if (empty($svertka) or $svertka=='-')
                    $na['NAME'] = trim((string)$ao['Naimenovanie']);
                else $na['NAME'] = $svertka;
                $na['CODE'] = trim((string)$ao['Artikul']);
                $na['PACK'] = trim((string)$ao['VUpakovke']);
                $vupakovke2 = trim((string)$ao['VUpakovke2']);

                if (!empty($vupakovke2)) $na['PACK'] .= '/' . $vupakovke2;
                $na['ED'] = $unit;
                if (empty($na['NAME']))
                    $na['NAME'] = $elementInfo['NAME'];
                $na['ID'] = (int)$ao['ID'];
                $na['PREVIEW_PICTURE'] = $elementInfo['PREVIEW_PICTURE'];
                //$na['PROPERTY_PRICE_VALUE'] = (string)$ao['CZena2'];
                $na['PROPERTY_PRICE_OPT_VALUE'] = (string)$ao['CZena2'];

                $result[] = $na;
            }

        }
        //msg($result);
        //exit;
        return $result;
    }

    public function getArray($from, $depthLevel = 0)
    {
        $depthLevel++;


        //msg($from); exit;


        /*
        if ($from->ID==4532) {
            msg($from);
        }
        */

        if (isset($from->ID)) {
            //msg('--------');
            //msg($from);
            //msg('--------');

            $from = array($from);
            //msg($from);
            //exit;
        }

        $from = (array)$from;

        foreach ($from as $f) {


            /*
            if ((int)$f->ID==4532) {
                msg($f);
                exit;
            }
            */

            $arFilter = Array('IBLOCK_ID' => 1, 'UF_ROWID' => (int)$f->ID);
            $db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_*"));
            $count = $db_list->SelectedRowsCount(); // количество полученных записей из таблицы


            if ($count == 1) {
                $ar_result = $db_list->GetNext();
                //msg($ar_result); exit;
                $na = array();
                //$sectionName = (string)$f->name;
                $sectionName = trim($ar_result['NAME']);

                $sectionName = preg_replace('/(^\.\s*[0-9]*\s*)/', '', $sectionName);
                $na['NAME'] = $sectionName;
                $na['ID'] = (int)$f->ID;
                $na['UF_TEMPLATE'] = $ar_result['UF_TEMPLATE'];
                $na['PICTURE'] = $ar_result['PICTURE'];


                if (!isset($f->childs)) {
                    //$na['DEPTH_LEVEL'] = $depthLevel;
                    $na['RIGHT_MARGIN'] = 2;
                    $na['LEFT_MARGIN'] = 1;
                } else {
                    //$na['DEPTH_LEVEL'] = 2;
                    //$na['DEPTH_LEVEL'] = $ar_result['DEPTH_LEVEL'];
                    $na['RIGHT_MARGIN'] = 20;
                    $na['LEFT_MARGIN'] = 10;
                }
                $na['DEPTH_LEVEL'] = $ar_result['DEPTH_LEVEL'];
                $this->collection[] = $na;
                if (isset($f->childs)) {

                    $xmlArray = (array)$f->childs;
                    //if ((int)$f->ID==18513) {
                    //msg('1853');
                    //msg($f);
                    //msg($xmlArray['category']);
                    //exit;
                    //}

                    $this->getArray($xmlArray['category'], $depthLevel);
                }
            }

        }
    }
}

$xml = '';
if (file_exists('tmp/cats2.xml')) {
    //msg('файл есть');
    $xml = simplexml_load_file("tmp/cats2.xml");
};
if (!$xml)
{
    //msg('файл отсутствует или пустой - генерируем');
    $xml = file_get_contents("http://strprofi.ru/import/export/cats2.php?SITE_ID=585");
    //msg($xml);
    $file = fopen("tmp/cats2.xml", "w+");
    fputs($file, (string)$xml);
    fclose($file);
    $xml = simplexml_load_file("tmp/cats2.xml");
}


//msg($xml);
//exit;

//$xml = simplexml_load_file('http://strprofi.ru/import/export/cats2.php?SITE_ID=585');


$xmlArray = (array)$xml;
//msg($xmlArray); exit;
$xmlToArray = new getArrayFromXML();
//msg($xmlArray['category']); exit;
$xmlToArray->getArray($xmlArray['category']);
$sections = $xmlToArray->getCollection();


//msg($sections);

//exit;


$arFilter = array('IBLOCK_ID' => 1, "ACTIVE" => "Y");
$db_list = CIBlockSection::GetList(Array("left_margin" => "asc"), $arFilter, true, array('UF_*'));
$sectionNumber = $db_list->SelectedRowsCount();


$contentsArray = array();

$sectionCount = 0;
$html = '';
$pagesCounter = 1;

$heightCounter = 0;
$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
$columnCounter = 0;
$tableHeight = 0;
$forceBreak = false;
$sectionOpen = false;

$pageTitle = '';
$firstPageHeader = '';

$rowHeight = $sizes['elementRow'] + $sizes['elementPaddingTop'] + $sizes['elementPaddingBottom'] + $sizes['elementBorderBottom'];

//while ($section = $db_list->GetNext()) {


//msg($sections); exit;


foreach ($sections as $section) {
    //if ($section['ID'] != 4532) continue;

    /*
    if ($section['ID']==2519) {
        msg($section);
        exit;
    }
    */


    if ($section['RIGHT_MARGIN'] - $section['LEFT_MARGIN'] == 1 /*and $section['ID']==18513*/) {



        $loadXML = '';
        if (file_exists('tmp/section-'.$section['ID'].'.xml')) {
            //if (false) {
            //msg('файл есть');
            $loadXML = simplexml_load_file('tmp/section-'.$section['ID'].'.xml');
        };
        if (!$loadXML)
        {
            //msg('файл отсутствует или пустой - генерируем');
            $loadXML = file_get_contents('http://strprofi.ru/import/export/items2.php?cat=' . $section['ID'] . '&SITE_ID=585');
            //msg($xml);
            $file = fopen('tmp/section-'.$section['ID'].'.xml', "w+");
            fputs($file, (string)$loadXML);
            fclose($file);
            $loadXML = simplexml_load_file('tmp/section-'.$section['ID'].'.xml');
        }

        //msg($loadXML); exit;

        //$loadXML = simplexml_load_file('http://strprofi.ru/import/export/items2.php?cat=' . $section['ID'] . '&SITE_ID=585');

        $elementsArray = $xmlToArray->getElementsArray($loadXML, $section['ID']);
        $elementsNumber = count($elementsArray);


        //msg($elementsArray); exit;


        if ($elementsNumber > 0) {


            //msg('самый глубокий уровень - не показыаем в содержании: '.$section['ID'].', '.$section['NAME']);
            //exit;

            $sectionCount++;
            $elementsCount = 0;
            $nextColumn = false;
            $eOnSectionCount = 0;


            $sectionImage = CFile::ResizeImageGet($section['PICTURE'], array('width' => 100, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true);
            //msg(CFile::GetPath($section['PICTURE'])); exit;

            //$sectionImgHTML .= $sizes['pageHeight'].', '.$heightCounter;
            $sectionName = iconv(mb_detect_encoding($section['NAME'], mb_detect_order(), true), "UTF-8", $section['NAME']);
            $sectionName = utf8_wordwrap($sectionName, $sizes['h3Break'], '<br/>');
            $lineNum = substr_count($sectionName, '<br/>') + 1;

            // смотрим - поместится ли заголовок и первые несколько строк таблицы на страницу
            $nextHeight = $heightCounter + ($sizes['h3'] * $lineNum) + $sizes['h3Margin'] + (5 * $rowHeight) + $rowHeight;

            if ($heightCounter >= $sizes['forceBreak'] or $nextHeight >= $sizes['forceBreak']) {

                $nextColumnBreak = true;

                if ($heightCounter >= $sizes['forceBreak'] or $nextHeight >= $sizes['forceBreak']) {

                    $forceBreak = true;
                }

                $columnCounter++;
                $tableHeight = 0;

                $heightCounter = 0;
                $heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);

                if ($columnCounter % 2 == 0) {
                    /*
                    * Разрыв страницы перед заголовком
                    */

                    $html .= '</div>';

                    $html .= '<div class="clear"></div><div class="page-footer">' . $pagesCounter . '</div></div><div class="page-break-after"></div>';
                    $pagesCounter++;
                    //$html .= '<div class="page-break-before"></div>';
                    //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                    $html .= '<div class="pageBox">';

                    $html .= '<div class="header"><img alt="" src="/img/orange.gif" class="header-background" /><div class="address">' . $addressHTML . '</div>';
                    $html .= '<h1 data-height="' . $heightCounter . '">' . $pageTitle . '</h1></div>';
                    if ($pageTitleID and !isset($contentsArray[$pageTitleID])) {
                        $contentsArray[$pageTitleID] = array(
                            'PAGE' => $pagesCounter,
                            'NAME' => $pageTitle,
                            'LEVEL' => 1
                        );
                    }
                    //$html .= '<hr />';
                    //$html .= '<div class="page-break-after"></div>';
                    $html .= '<div class="firstColumn">';

                    //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                } else {
                    /**
                     * Другая колонка перед заголовком
                     */
                    $html .= '</div>';
                    $html .= '<div class="sectionsColumn">';
                    //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                }


                $nextColumn = true;

            } else {
                $nextColumnBreak = false;
            }
            $padding = '';
            if ($section['UF_TEMPLATE'] == 0) {
                $padding = 'no-padding';
            }


            /*
            * Заголовок раздела
            */


            $heightCounter = $heightCounter + ($sizes['h3'] * $lineNum) + $sizes['h3Margin'];


            if ($forceBreak) {
                //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
            }

            $html .= '<div class="h3-box"><h3 data-height="' . $heightCounter . '" data-next-column-break="' . $nextColumnBreak . '" data-force-break="' . $forceBreak . '" data-test="обычный текущий заголовок"  data-lines="' . $lineNum . '" data-id="' . $section['ID'] . '" data-strlen="' . $strlen . '">' . $sectionName . '<!--' . $heightCounter . ', ' . $elementsNumber . ' --></h3></div>';
            $h3Show = true;

            $html .= '<div data-sectionid="' . $section['ID'] . '" class="section ' . $padding . '">';
            $sectionOpen = true;


            $html .= '<table>';
            $html .= '<tr><td class="data-td"><table>';


            /*
            * Шапка таблицы
            */
            $firstTd = 'first-td';
            // Высота шапки таблицы = высота строки + бордер сверху (такой-же как и бордер снизу)
            $heightCounter = $heightCounter + $rowHeight + $sizes['elementBorderBottom'];
            $html .= '<tr data-height="' . $heightCounter . '" data-row-height="' . $rowHeight . '" class="first-tr">';
            if ($section['UF_TEMPLATE'] == 0) {
                $firstTd = '';
                $html .= '<td class="first-td content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif"><div>Картинка товара</div></div></td>';
            }
            $html .= '<td class="content ' . $firstTd . '"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Арт.</div></div></td>';
            $html .= '<td class="content "><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Название <!--' . $heightCounter . '--></div></div></td>';
            $html .= '<td class="content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Упак.</div></div></td>';
            $html .= '<td class="content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Ед.</div></div></td>';
            $html .= '<td class="content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Цена</div></div></td></tr>';
            $forceBreak = false;


            if ($forceBreak) {

            } else {
                //$forceBreak = false;
            }


            //while ($element = $res->GetNext()) {
            foreach ($elementsArray as $element) {

                //msg($element['PREVIEW_PICTURE']); exit;
                //msg(CFile::GetPath($element['PREVIEW_PICTURE']));
                $elementImage = CFile::ResizeImageGet($element['PREVIEW_PICTURE'], array('width' => 100, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true);

                $elementsCount++;
                $eOnSectionCount++;


                $elementName = $element['NAME'];
                $elementName = iconv(mb_detect_encoding($elementName, mb_detect_order(), true), "UTF-8", $elementName);
                $elementName = utf8_wordwrap($elementName, 25, '<br/>');
                $elementNameLineNum = substr_count($elementName, '<br/>') + 1;
                if ($section['UF_TEMPLATE'] == 0) {
                    $nextRowHeightCounter = $heightCounter + 22 + $sizes['elementBorderBottom'];
                    //$tableHeight = $tableHeight + 22 + $sizes['elementBorderBottom'];
                } else {
                    $thisRowHeight = ($sizes['elementRow'] * $elementNameLineNum) + $sizes['elementBorderBottom'] + $sizes['elementPaddingBottom'] + $sizes['elementPaddingTop'];
                    $nextRowHeightCounter = $heightCounter + $thisRowHeight;
                    //$tableHeight = $tableHeight + $thisRowHeight;
                }


                //$nextRowHeight =

                $forceBreakSize = $sizes['forceBreakSmall'];
                if ($section['UF_TEMPLATE']==0) {
                    $forceBreakSize = $sizes['forceBreak'];
                }

                if ($nextRowHeightCounter >= $forceBreakSize) {
                    //if ($heightCounter >= $sizes['forceBreak']) {
                    $columnCounter++;

                    $sectionImgHTML = '';
                    if ($sectionImage['src']) $sectionImgHTML = '<img alt="" src="' . $sectionImage['src'] . '" style="max-height:' . ($tableHeight-$rowHeight-$sizes['elementBorderBottom']-2) . 'mm" />';

                    $heightCounter = 0;
                    $heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                    if ($columnCounter % 2 == 0) {
                        /*
                        * Разрыв страницы во время таблицы
                        */
                        $html .= '</table></td></tr></table>';
                        if ($section['UF_TEMPLATE'] != 0) {
                            if ($h3Show) $html .= '<div class="section-image-box" data-test="' . $tableHeight . '">' . $sectionImgHTML . '</div>';
                        }

                        $html .= '</div>';
                        //$html .= $heightCounter.', '.$sizes['pageHeight'];

                        $html .= '</div>';

                        $html .= '<div class="clear"></div><div class="page-footer">' . $pagesCounter . '</div></div><div class="page-break-after "></div>';
                        $pagesCounter++;
                        //$html .= '<div class="page-break-before"></div>';
                        //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                        $html .= '<div class="pageBox">';

                        $html .= '<div class="header"><img alt="" src="/img/orange.gif" class="header-background" /><div class="address">' . $addressHTML . '</div>';
                        $html .= '<h1 data-height="' . $heightCounter . '">' . $pageTitle . '</h1></div>';
                        if ($pageTitleID and !isset($contentsArray[$pageTitleID])) {
                            $contentsArray[$pageTitleID] = array(
                                'PAGE' => $pagesCounter,
                                'NAME' => $pageTitle,
                                'LEVEL' => 1
                            );
                        }
                        //$html .= '<hr />';
                        //$html .= '<div class="page-break-after"></div>';

                        $html .= '<div class="firstColumn">';

                        //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                    } else {
                        /**
                         * Другая колонка во время таблицы
                         */
                        $html .= '</table></td></tr></table>';
                        if ($section['UF_TEMPLATE'] != 0) {
                            if ($h3Show) $html .= '<div class="section-image-box" data-test="' . $tableHeight . '">' . $sectionImgHTML . '</div>';
                        }
                        $html .= '</div>';
                        //$html .= $heightCounter.', '.$sizes['pageHeight'];


                        $html .= '</div>';
                        $html .= '<div class="sectionsColumn">';
                        //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                    }
                    $tableHeight = 0;
                    $sectionImgHTML = '';
                    if ($sectionImage['src'])
                        $sectionImgHTML = '<img alt="" src="' . $sectionImage['src'] . '" style="max-height:50mm" />';
                    if ((($elementsNumber + 1) - $eOnSectionCount) == 0)
                        $sectionOpen = false;
                    else $sectionOpen = true;
                    if ($sectionOpen) {


                        $sectionNameHTML = '';
                        $sectionName = iconv(mb_detect_encoding($section['NAME'], mb_detect_order(), true), "UTF-8", $section['NAME']);
                        $sectionName = utf8_wordwrap($sectionName, $sizes['h3Break'], '<br/>');
                        $lineNum = substr_count($sectionName, '<br/>') + 1;

                        //$heightCounter = $heightCounter + ($sizes['h3'] * $lineNum) + $sizes['h3Margin'];
                        //$sectionNameHTML .= '<div class="h3-box"><h3 data-test="заголовок на новой странице или в новой колонке" data-height="'.$heightCounter.' data-lines="'.$lineNum.'" data-strlen="'.$strlen.'">' . $sectionName . '<!--'.$heightCounter.', (' . $eOnSectionCount . ',  ' . $elementsNumber . ')-->';
                        //$sectionNameHTML .= '</h3></div>';
                        $h3Show = false;


                        $padding = '';
                        if ($section['UF_TEMPLATE'] == 0) {
                            $padding = 'no-padding';
                        }

                        $html .= $sectionNameHTML;
                        $html .= '<div data-section-id="' . $section['ID'] . '" class="section ' . $padding . '">';
                        $html .= '<table><tr><td class="data-td"><table>';


                    } else {
                        $h3Show = true;
                    }


                    //$heightCounter = $heightCounter + $sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom'];

                    //$nextColumn = true;

                    // Шапка таблицы в новой колонке или на новой странице


                    $heightCounter = $heightCounter + $rowHeight + $sizes['elementBorderBottom'];
                    $html .= "\n\n\n" . '<tr data-height="' . $heightCounter . '" data-info1="' . $eOnSectionCount . '??' . $elementsNumber . '" data-section-open="' . $sectionOpen . '" data-h3-show="' . $h3Show . '" class="first-tr" data-info="Шапка таблицы в новой колонке или на новой странице">';
                    if ($section['UF_TEMPLATE'] == 0) {
                        $firstTd = '';
                        $html .= '<td class="first-td content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif"><div>Картинка товара</div></div></td>';
                    }
                    $html .= '<td class="content ' . $firstTd . '"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Арт.</div></div></td>';
                    $html .= '<td class="content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Название<!--, ' . $heightCounter . '--></div></div></td>';
                    $html .= '<td class="content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Упак.</div></div></td>';
                    $html .= '<td class="content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Ед.</div></div></td>';
                    $html .= '<td class="content"><div class="td-content"><img class="th-background" alt="" src="/img/eee.gif" /><div>Цена</div></div></td></tr>';

                }


                // Формирование строки

                $elementName = $element['NAME'];
                $elementName = iconv(mb_detect_encoding($elementName, mb_detect_order(), true), "UTF-8", $elementName);
                $elementName = utf8_wordwrap($elementName, 25, '<br/>');
                $elementNameLineNum = substr_count($elementName, '<br/>') + 1;

                if ($section['UF_TEMPLATE'] == 0) {
                    $thisRowHeight = 22 + $sizes['elementBorderBottom'];
                    $heightCounter = $heightCounter + $thisRowHeight;
                    $tableHeight = $tableHeight + $thisRowHeight;
                } else {
                    $thisRowHeight = ($sizes['elementRow'] * $elementNameLineNum) + $sizes['elementBorderBottom'] + $sizes['elementPaddingBottom'] + $sizes['elementPaddingTop'];
                    $heightCounter = $heightCounter + $thisRowHeight;
                    $tableHeight = $tableHeight + $thisRowHeight;
                }

                $elementImageHTML = '';
                $firstTd = 'first-td';
                if ($section['UF_TEMPLATE'] == 0) {
                    $ufIMG = '';
                    if ($elementImage['src']) $ufIMG = '<img alt="" src="' . $elementImage['src'] . '" />';
                    $elementImageHTML = '<td class="content first-td element-image"><div class="element-image-box">' . $ufIMG . '</div></td>';
                    $firstTd = '';
                }
                $elementCodeHTML = '<td class="content elementCode ' . $firstTd . '"><div class="td-content nowrap">' . $element['CODE'] . '</div></td>';
                $elementNameHTML = '<td class="content elementName"><div class="td-content"><div class="pos"><!--' . $eOnSectionCount . '.--><!--(' . $heightCounter . ') (' . $tableHeight . ')-->' . $elementName . '</div></div></td>';
                //$elementPriceHTML = '<td class="content elementPrice"><div class="td-content">' . $element['PROPERTY_PRICE_VALUE'] . '</div></td>';
                $ed = '<td class="content elementEd"><div class="td-content nowrap">' . $element['ED'] . '</div></td>';
                $elementPackHTML = '<td class="content elementPack"><div class="td-content nowrap">' . $element['PACK'] . '</div></td>';
                $elementOptHTML = '<td class="content elementOpt"><div class="td-content nowrap">' . $element['PROPERTY_PRICE_OPT_VALUE'] . '</div></td>';
                $html .= '<tr data-row-height="'.$thisRowHeight.'" data-height="' . $heightCounter . '" data-info="Строка товара">' . $elementImageHTML . $elementCodeHTML . $elementNameHTML . $elementPackHTML . $ed . $elementOptHTML . '</tr>';


            }


            if ($sectionOpen) {

                if ($tableHeight < 31 and $section['UF_TEMPLATE'] != 0) {
                    if ((5 - $eOnSectionCount) > 0) {
                        $emptyHeight = ($thisRowHeight * (5 - $eOnSectionCount));
                        $heightCounter = $heightCounter + $emptyHeight;
                        $tableHeight = $tableHeight + $emptyHeight;
                        $tdr = 4;

                        $tdRepeat = str_repeat('<td class="content"><div class="td-content nowrap">&nbsp;</div></td>', $tdr);
                        $html .= str_repeat('<tr data-row-height="'.$thisRowHeight.'" data-empty="'.$emptyHeight.'" class="empty"><td class="content first-td"><div class="td-content nowrap">&nbsp;</div></td>' . $tdRepeat . '</tr>', (5 - $eOnSectionCount));
                        //$html .= '<tr><td colspan="5">'.(5-$eOnSectionCount).'</td></tr>';
                    }

                }

                $sectionImgHTML = '';
                if ($sectionImage['src'])
                    $sectionImgHTML = '<img alt="" src="' . $sectionImage['src'] . '" style="max-height:' . ($tableHeight-$rowHeight-$sizes['elementBorderBottom']-2) . 'mm" />';

                $html .= '</table></td></tr></table>';
                if ($section['UF_TEMPLATE'] != 0) {
                    if ($h3Show) $html .= '<div class="section-image-box" data-test="высота обычной таблицы: ' . $tableHeight . ', высота строки в этой таблице: ' . $thisRowHeight . ', количество элементов в разделе: ' . $eOnSectionCount . '">' . $sectionImgHTML . '</div>';
                }

                $html .= '</div>' . "\n";


            }


            $tableHeight = 0;

            //if ($sectionCount == 100) break;


            if ($sectionCount != $sectionNumber) {


                if ($heightCounter >= $sizes['pageHeight']) {

                    $columnCounter++;
                    $tableHeight = 0;
                    $heightCounter = 0;
                    $heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);


                    $html .= '</div>';
                    if ($columnCounter % 2 == 0) {
                        /*
                        * Разрыв страницы
                        */
                        //$html .= '</div>';

                        $html .= '<div class="clear"></div><div class="page-footer">' . $pagesCounter . '</div></div><div class="page-break-after "></div>';
                        $pagesCounter++;
                        //$html .= '<div class="page-break-before"></div>';
                        //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                        //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                        $html .= '<div class="pageBox">';

                        $html .= '<div class="header"><img alt="" src="/img/orange.gif" class="header-background" /><div class="address">' . $addressHTML . '</div>';
                        $html .= '<h1 data-height="' . $heightCounter . '">' . $pageTitle . '</h1></div>';
                        if ($pageTitleID and !isset($contentsArray[$pageTitleID])) {
                            $contentsArray[$pageTitleID] = array(
                                'PAGE' => $pagesCounter,
                                'NAME' => $pageTitle,
                                'LEVEL' => 1
                            );
                        }
                        //$html .= '<hr />';
                        //$html .= '<div class="page-break-after"></div>';
                        $html .= '<div class="firstColumn">';


                    } else {
                        /**
                         * Другая колонка
                         */
                        $html .= '<div class="sectionsColumn">';
                        //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                    }

                    $nextColumn = true;

                } else {

                    if ($sectionOpen) {
                        $heightCounter = $heightCounter + $sizes['sectionDivider'];
                        $html .= '<div class="sectionDivider" data-height="' . $heightCounter . '"></div>';
                    }

                }


            }
        }

    } else {

        //msg($section); exit;

        if ($section['DEPTH_LEVEL'] > 1) {

            if ($forceNewPage) {
                $html .= '';
            }

            $h2Name = iconv(mb_detect_encoding($section['NAME'], mb_detect_order(), true), "UTF-8", $section['NAME']);
            $h2Name = utf8_wordwrap($h2Name, $sizes['h2Break'], '<br/>');
            $lineNum = substr_count($h2Name, '<br/>') + 1;

            $nextHeight = $heightCounter + ($sizes['h2'] * $lineNum) + $sizes['h2Margin'] + (5 * $rowHeight) + $rowHeight;


            if ($heightCounter >= ($sizes['forceBreak']) or $nextHeight >= ($sizes['forceBreak'])) {
                $heightCounter = 0;
                $heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                if ($columnCounter % 2 == 0) {

                    $html .= '</div>';
                    $html .= '<div class="sectionsColumn">';
                    //$heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                    //$html .= '';
                } else {

                    $html .= '</div>';

                    $html .= '<div class="clear"></div><div class="page-footer">' . $pagesCounter . '</div></div><div class="page-break-after "></div>';
                    $pagesCounter++;
                    //$html .= '<hr />';
                    //$html .= '<div class="page-break-after"></div>';
                    $heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                    $html .= '<div class="pageBox">';
                    $html .= '<div class="header"><img alt="" src="/img/orange.gif" class="header-background" /><div class="address">' . $addressHTML . '</div>';
                    $html .= '<h1 data-height="' . $heightCounter . '">' . $pageTitle . '</h1></div>';
                    $html .= '<div class="firstColumn">';


                }
                $columnCounter++;

            }




            $heightCounter = $heightCounter + ($sizes['h2'] * $lineNum) + $sizes['h2Margin'];
            $html .= '<div class="h2-box"><img class="h2-background" src="/img/h2-background.gif" alt="" /><h2 data-id="' . $section['ID'] . '" data-height="' . $heightCounter . '" data-depth-level="' . $section['DEPTH_LEVEL'] . '">' . $h2Name . '</h2></div>';
            if ($section['ID'] and !isset($contentsArray[$section['ID']])) {
                $ca = array(
                    'PAGE' => $pagesCounter,
                    'NAME' => $section['NAME'],
                    'LEVEL' => $section['DEPTH_LEVEL']
                );
                $contentsArray[$section['ID']] = $ca;
                //msg($ca);
            }

        } else {


            if ($sectionCount == 0) {
                $firstPageHeader = '<div class="pageBox">';
                $firstPageHeader .= '<div class="header"><img alt="" src="/img/orange.gif" class="header-background" /><div class="address">' . $addressHTML . '</div>';
                $firstPageHeader .= '<h1 data-height="' . $heightCounter . '">' . $section['NAME'] . '</h1></div>';
                $forceNewPage = false;
            } else {

                //$html .= '</div>';
                $heightCounter = 0;

                $heightCounter = $heightCounter + ($sizes['pageHeaderHeight'] + $sizes['pageHeaderMarginBottom']);
                $html .= '</div><div class="clear"></div><div class="page-footer">' . $pagesCounter . '</div></div><div class="page-break-after "></div>';
                $html .= "<!-- ---------------------- -->\n\n\n\n\n\n\n\n";
                $html .= '<div class="pageBox">';
                $html .= '<div class="header"><img alt="" src="/img/orange.gif" class="header-background" /><div class="address">' . $addressHTML . '</div>';
                $html .= '<h1 data-height="' . $heightCounter . '">' . $section['NAME'] . '</h1></div><div class="firstColumn">';
                $forceNewPage = true;
                $pagesCounter++;

                $columnCounter = 0;
            }


            $pageTitle = $section['NAME'];
            $pageTitleID = $section['ID'];
            if ($pageTitleID and !isset($contentsArray[$pageTitleID])) {
                $ca = array(
                    'PAGE' => $pagesCounter,
                    'NAME' => $pageTitle,
                    'LEVEL' => 1
                );
                $contentsArray[$pageTitleID] = $ca;
                //msg($ca);
            }


        }
    }


    //}
}

$html .= '</div><div class="clear" style="page-break-after: auto;"></div><div class="page-footer">' . $pagesCounter . '</div></div><!--<hr/>--></div>';
$html .= "\n" . '';

$html = '<div class="sectionsBox">' . $firstPageHeader . '<div class="firstColumn">' . '' . $html;

//msg($contentsArray);

$contents = '<div id="contents"><div class="contentsTitle">Содержание</div>';
$contents .= '<table>';
foreach ($contentsArray as $sectionInfo) {
    $contents .= '<tr class="h' . $sectionInfo['LEVEL'] . '"><td class="title"><div>' . $sectionInfo['NAME'] . '</div></td><td class="bordered">&nbsp;</td><td class="page">' . $sectionInfo['PAGE'] . '</td></tr>';
}
//$contents .= '<pre>'.print_r($contentsArray, true).'</pre>';
$contents .= '</table></div>';
$contents .= '<div class="clear"></div><div class="page-break-after"></div>';

$firstList = '<div id="first-list">

<div class="logoBox"><img alt="" src="/img/big-logo.png" /></div>

<div class="titleName">Прайс-лист</div>

<div class="desc">
<p>Уважаемые партнеры реализована возможность выполнять заказы
через  сайт strprofi.ru, для этого не требуется специальных навыков
необходимо набрать корзину (по принципу интернет магазина) и
оформить заказ.</p>
<p>При заказе через сайт</p>
<ul>
<li>
экономится время на составлении заказа
</li>
<li>исключаются возможные ошибки и разночтения</li>
<li>Вы получаете актуальную информацию по ассортименту, ценам и текущим складским остаткам</li>

<li>Вы получаете дополнительную скидку 2%</li>
</ul>
</div>

</div>
<div class="page-break-after"></div>';


echo $firstList . $contents . $html;


?>


</body>
</html>
