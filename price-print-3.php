<!doctype html>
<html>
<head>
    <title>Прайс-лист</title>
    <style type="text/css" media="all">
        body {
            font-family: Arial, sans-serif;
            font-size: 7pt;
        }

        .page-break-after {
            page-break-after: always;
        }

        .page-break-before {
            page-break-before: always;
        }

        td {
            vertical-align: top;
            border-bottom: 0.5mm solid #000;
            border-right: 0.5mm solid #000;
            height: 3mm;
            line-height: 3mm;
        }

        td.first {
            border-top: 0.5mm solid #000;
        }

        td.image {
            border-left: 0.5mm solid #000;
        }

        tbody {
            border-bottom: 0.5mm solid #000;
            border-left: 0.5mm solid #000;
        }

        tr {
            /*border-top: 0.5mm solid #000;*/

        }

        .border-left {
            border-left: 0.5mm solid #000;
        }

        .border-right {
            border-right: 0.5mm solid #000;
        }

        .border-top {
            border-top: 0.5mm solid #000;
        }

        table.parent {
            border: none;
            margin-bottom: 1cm;
            border-collapse: collapse;
        }

        table.parent tbody {
            border: none;
        }

        tr.parent {
            border: none;
            padding: 0
        }

        td.parent {
            border: none;
            padding: 0;
        }

        table, td {
            padding: 0;
        }

        table {
            /*background: #000;*/
            width: 100%;
            border-collapse: collapse;
            empty-cells: show;
        }

        td {
            /*background: #fff;*/
            padding: 1mm;
        }

        .no-border {
            border: none;
        }

        .section-table {
            margin-bottom: 5mm;
        }

        .section-table tbody {
            border-top: 0.5mm solid #000;
        }

        .no-padding {
            padding: 0;
        }
    </style>
</head>
<body>


<?


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");



function getRowsPerPage($additionaly = 0){

    $rowsPerPage = 45;

    $result = $rowsPerPage - $additionaly;

    return $result;
}






//echo '<h1>Прайс-лист</h1>';

//echo (45%45).'<hr/>';

//echo (90 % 90) . '<hr/>';


$arFilter = array('IBLOCK_ID' => 1, "ACTIVE" => "Y");
$db_list = CIBlockSection::GetList(Array("left_margin" => "asc"), $arFilter, true, array('UF_*'));


$cc = 0;
$ce = 0;
$sectionsInColCounter = 0;
$nnce = 0;
$nce = 0;
$html = '<table class="root-table"><tbody class="no-border"><tr><td class="no-padding no-border" style="width: 50%;">';
$rowspanCount = 0;
$prevRowspan = 0;
$tablesPerColumn = 0;

while ($ar_result = $db_list->GetNext()) {
    //msg($ar_result); exit;
    $hn = $ar_result['DEPTH_LEVEL'] + 1;
    if ($ar_result['RIGHT_MARGIN'] - $ar_result['LEFT_MARGIN'] == 1 /*and ($ar_result['ID']==3243)*/) {
        $rowspanPerSection = 0;
        $toCol = '';
        $sectionsInColCounter++;
        $cc++;
        $arSelect = Array("ID", "NAME", "PROPERTY_PRICE", "PROPERTY_PRICE_OPT");
        $arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "SECTION_ID" => $ar_result['ID']);
        $res = CIBlockElement::GetList(Array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
        $count = $res->SelectedRowsCount();
        //msg($count);
        $elementsRound = round($count / 2) + 1;
        $diff = $count - $elementsRound;
        $ndiff = $elementsRound - $diff - 2;
        if ($cc == 1) {
            //$nce = $nce + 2;
        } else {
            //$nce = $nce + 3;
            $tablesPerColumn++;
            $html .= '</tbody></table>';
        }

        $sectionImage = CFile::ResizeImageGet($ar_result['PICTURE'], array('width' => 100, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true);
        $html .= "\n\n" . '<table class="section-table" style="width: 100%;"><tbody>';
        $rowsPerTable = 0;
        $rowsPerPartOfTable = 0;
        $prevRowspan = 0;
        $rowspanCount = 0;
        if ($ar_result['UF_TEMPLATE'] == 0) {

        } else {
            $cce = 0;
            $rowspan = $count;
            $nrowspan = getRowsPerPage() - ($nce % getRowsPerPage());
            $pageBreak = false;
            if ($count > $nrowspan) {
                $rowspan = $nrowspan; // это важно
            }
            $rowspan = $rowspan + 1;
            $prevRowspan = $rowspan;
            $rowspanCount = $rowspanCount + $prevRowspan;
            $rowspanPerSection = $rowspanPerSection + $rowspan - 1;
            $additionalyRows = ($tablesPerColumn * 3) + 2;

            $oce = 0; // количество строк в каждой таблице

            $tableName = $ar_result['NAME'];
            $tableName .= ' ';
            $tableName .= ' (количество элементов в таблице: ' . ($count) . '),';
            $tableName .= ' количество дополнительных строк в колонке: '.$additionalyRows.' ';
            $tableName .= ' $nrowspan="' . $nrowspan . '", $rowspan="' . $rowspan . '", $rowspanPerSection="' . $rowspanPerSection . '"';

            $html .= '<tr><td colspan="4" class="tableName">' . $tableName . '</td></tr>';
            $html .=
                '<tr><td rowspan="' . ($rowspan) . '" style="text-align:center; width:1%;">'
                    . '<div style="width:100px;"><img src="' . $sectionImage['src'] . '" alt="" /></div>'
                    . '</td><td>Наименование</td><td>Опт</td><td>Розница</td></tr>';


            while ($ob = $res->GetNext()) {
                $rowsPerTable++;
                $rowsPerPartOfTable++;
                $oce++;
                //$nnce++;


                $cce++;

                $ce++;

                $nce++;
                //$firstTr = '';

                //$nce = ($sectionsInColCounter * 3) + $nnce;

                $elementName = '(' . $rowsPerPartOfTable . ', ' . $rowsPerTable . ', $oce="' . $oce . '", ' . $ce . ', ' . $nce . ') ' . $ob['NAME'] . '';
                $nameBox = '<div style="height:3mm;position:absolute; width:100%; overflow:hidden; ">' . $elementName . '</div>';
                if ($toCol) {
                    $html .= '<tr><td colspan="4">'.$ar_result['NAME'].'</td></tr>';
                    $html .= '<tr>' . $toCol . '<td>Наименование</td><td>Опт</td><td>Розница</td></tr>';
                }
                $html .= '<tr><td><div style="width:100%; position:relative; height:3mm;">' . $nameBox . '</div></td><td style="width:10%;">' . $ob['PROPERTY_PRICE_OPT_VALUE'] . '</td><td style="width:10%;">' . $ob['PROPERTY_PRICE_VALUE'] . '</td></tr>';


                //if ($ce % $rowsPerPage == 0 and $ce % 90 != 0) {
                $toCol = false;


                if ($nce % (getRowsPerPage() * 2) == getRowsPerPage()) {

                    /*
                     * Переход в другую колонку
                     */

                    $tablesPerColumn = 0;


                    $newRowspan = $count - $oce;
                    $newRowspan++;


                    $html .= '</table></td>';
                    $html .= '<td class="no-padding no-border" style="padding-left: 5mm;">';
                    //$html .= 'переход на другую колонку ' . $nce . ', ' . ($nce % getRowsPerPage()) . ', $rowspanPerSection="' . $rowspanPerSection . '"';
                    //$html .= ' $newRowspan="' . $newRowspan . '" $oce="' . $oce . '" ';
                    //$html .= ' $tablesPerColumn="'.$tablesPerColumn.'" ';
                    $html .= ' количество доп. строк = '.$additionalyRows.' ';
                    $html .= '<table class="section-table" style="width: 100%;">';

                    $rowsPerPartOfTable = 0;


                    $partRowspan = $count - $prevRowspan + 1;

                    if ($partRowspan > getRowsPerPage()) {
                        $partRowspan = getRowsPerPage();
                        $rowspan = getRowsPerPage();
                    }

                    $rowspanPerSection = $rowspanPerSection + $rowspan;
                    $partRowspan = $count - $rowspanPerSection;
                    if ($partRowspan > getRowsPerPage()) {
                        $partRowspan = getRowsPerPage();
                    }
                    //$partRowspan = $partRowspan;


                    //$rowspanHTML = ' rowspan="'.$partRowspan.'"';
                    if ($partRowspan <= 1) {
                        $rowspanHTML = ' rowspan="0" ';
                    }

                    $rowspanHTML = ' rowspan="' . $newRowspan . '"';
                    if ($newRowspan <= 1) {
                        $rowspanHTML = '  ';
                    }


                    $toColAttributes = ' data-section-count="' . $count . '" data-rowspan-per-section="' . $rowspanPerSection . '" ';
                    $toColAttributes .= ' data-part-rowspan="' . $partRowspan . '" ';
                    $toCol = ' <td class="toCol" ' . $toColAttributes . ' ' . $rowspanHTML . '  style="text-align:center; width:1%;">';
                    $toCol .= '<div style="width:100px;"></div>';
                    $toCol .= '</td>';
                    $prevRowspan = $rowspan + $prevRowspan;

                    $sectionsInColCounter = 0;
                } else {

                }


                if ($nce % (getRowsPerPage() * 2) == 0) {

                    /*
                    * Разрыв страницы
                    */

                    $tablesPerColumn = 0;


                    $html .= "\n" . '</table></td></tr></tbody></table><div class="page-break-after">';
                    //$html .= 'Конец страницы (' . $nce . ', ' . ($nce % 90) . ')';
                    $html .= ' добавочные строки = '.$additionalyRows.' ';
                    $html .= '</div>';
                    $html .= "\n\n" . '<table class="root-table"><tbody class="no-border"><tr><td class="no-padding no-border" style="width: 50%;">';


                    $rowspan = $count - $oce;

                    if ($rowspan > getRowsPerPage()) $rowspan = getRowsPerPage();

                    $rowspan++;

                    //if ($rowspan > getRowsPerPage()) $rowspan = getRowsPerPage() + 1;

                    $rowspanPerSection = $rowspanPerSection + $rowspan;

                    if ($oce != $count) {

                        $html .= '<table class="section-table" style="width: 100%;">';
                        $rowsPerPartOfTable = 0;
                        $tableName2 = $ar_result['NAME'];
                        $tableName2 .= ' НОВАЯ СТРАНИЦА ';
                        $tableName2 .= '  $oce="' . $oce . '" ';
                        $tableName2 .= ', $count="' . $count . '", $prevRowspan="' . $prevRowspan . '", $rowspan="' . $rowspan . '"';
                        $tableName2 .= ' $rowspanPerSection="' . $rowspanPerSection . '"';
                        $html .= '<tr><td colspan="4" class="tableName">' . $tableName2 . '</td></tr>';
                        $html .= '<tr><td rowspan="' . $rowspan . '" style="width:1%; text-align:center;"><div style="width:100px;"><!--' . $sectionsInColCounter . '--><img src="' . $sectionImage['src'] . '" alt="" /></div></td><td> Наименование</td><td>Опт</td><td>Розница</td></tr>';
                        $rowsPerTable = 0;
                    }

                    $cce = 0;
                    $sectionsInColCounter = 0;
                }

            }
        }
        /*
               if ($oce == $count)
                  $html .= '</table>';
              */

        if ($cc == 25) break;
    }
    /*
          if ($ar_result['DEPTH_LEVEL'] < 3 and $cc > 2) {
         $html .= '<hr class="page-break-after" />';
         }
         */
}
$html .= '</tbody></table>';
$html .= "\n" . '</td></tr></tbody></table>';

echo $html;


?>


</body>
</html>
