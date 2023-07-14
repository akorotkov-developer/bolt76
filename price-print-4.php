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
            border-spacing: 0;
        }

        td {
            /*background: #fff;*/
            padding: 1mm;
        }

        .no-border {
            border: none;
        }

        .section-root-table {
            /*margin-bottom: 4.4mm;*/
        }

        .section-table tbody {
            /*border-top: 0.5mm solid #000;*/
            border-bottom: none;
        }

        .no-padding {
            padding: 0;
        }

        .w1 {
            width: 1%;
        }

        .section-image-box {
            width: 100px;
            text-align: center;
        }

        .border-right-none {
            border-right: none;
        }

        .last td {
            border-bottom: 0;
        }

        .tableName {
            text-align: center;
        }

        .table-divider{
            height: 4.5mm;
        }

    </style>
</head>
<body>


<?


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");



function getRowsPerPage($additionaly = 0)
{

    $rowsPerPage = 45;

    $result = $rowsPerPage - $additionaly;

    return $result;
}



$arFilter = array('IBLOCK_ID' => 1, "ACTIVE" => "Y");
$db_list = CIBlockSection::GetList(Array("left_margin" => "asc"), $arFilter, true, array('UF_*'));



$html = '<table class="root-table"><tbody class="no-border"><tr><td class="no-padding no-border" style="width: 50%;">';


$cc = 0;
$nce = 0;
$additionalyRows = 0;
$rowsPerColumn = 0;
$columnBreak = false;
$column1Rows = 0;

while ($ar_result = $db_list->GetNext()) {


    if ($ar_result['RIGHT_MARGIN'] - $ar_result['LEFT_MARGIN'] == 1) {

        $cc++;
        $rowsPerTable = 0;
        $toCol = false;

        //$addRows = 3;
        //if ($cc == 1) $addRows = 2;
        //$additionalyRows = $additionalyRows + $addRows;

        $arSelect = Array("ID", "NAME", "PROPERTY_PRICE", "PROPERTY_PRICE_OPT");
        $arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "SECTION_ID" => $ar_result['ID']);
        $res = CIBlockElement::GetList(Array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
        $count = $res->SelectedRowsCount();


        $sectionImage = CFile::ResizeImageGet($ar_result['PICTURE'], array('width' => 100, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true);

        if ($cc != 1) {
            $html .= '</tbody></table></td></tr></tbody></table>';
            $html .= '<div class="table-divider"></div>';
            $additionalyRows++;
        }

        $html .= "\n\n" . '<table class="section-root-table"><tbody class="border-top"><tr>';
        $html .= '<td class="w1 border-left border-right-none"><div class="section-image-box"><img alt="" src="' . $sectionImage['src'] . '" /></div></td>';
        $html .= '<td class="no-border no-padding">';
        $html .= '<table class="section-table"><tbody>';


        if ($ar_result['UF_TEMPLATE'] == 0) {

        } else {



            $tableName = '<div style="position:relative; height:3mm;"><div style="position:absolute; height:3mm; overflow:hidden; width:100%;">'.$ar_result['NAME'];
            //$tableName .= ' $count="' . $count . '" ';
            $tableName .= ' $additionalyRows="'.$additionalyRows.'" $column1Rows="'.$column1Rows.'" ';
            //if ($pageBreak) $tableName .= ' ('.$additionalyRows.') ';
            $tableName .= '</div></div>';

            $html .= '<tr><td colspan="3" class="tableName">' . $tableName . '</td></tr>';
            $html .= '<tr><td>Наименование</td><td>Опт</td><td>Розница</td></tr>';


            while ($ob = $res->GetNext()) {
                $rowsPerColumn++;
                $rowsPerTable++;
                $nce++;
                $toCol = false;


                if ($nce % ((getRowsPerPage()-$additionalyRows) * 2) == (getRowsPerPage()-$additionalyRows)) {
                    $columnBreak = true;
                }

                $pageBreak = false;
                if ($nce % (getRowsPerPage() * 2) == 0) {
                    //if ($nce % ((getRowsPerPage() * 2)-$additionalyRows) == 0) {

                    $pageBreak = true;
                }




                $lastTr = '';
                if ($rowsPerTable == $count or $pageBreak or $columnBreak) {
                    $lastTr = ' class="last"';
                }


                $elementName = $ob['NAME'];
                $elementName .= ' $rowsPerTable="' . $rowsPerTable . '"  $nce="' . $nce . '"  $rowsPerColumn="'.$rowsPerColumn.'" ';
                $nameBox = '<div style="height:3mm;position:absolute; width:100%; overflow:hidden; white-space:nowrap; ">' . $elementName . '</div>';
                $html .= '<tr' . $lastTr . '><td><div style="width:100%; position:relative; height:3mm;">' . $nameBox . '</div></td>';
                $html .= '<td style="width:10%;">' . $ob['PROPERTY_PRICE_OPT_VALUE'] . '</td>';
                $html .= '<td style="width:10%;">' . $ob['PROPERTY_PRICE_VALUE'] . '</td></tr>';


                if ($columnBreak) {
                    $columnBreak = false;
                    $column1Rows = $additionalyRows;
                    $rowsPerColumn = 0;

                    /*
                     * Переход в другую колонку
                     */



                    //$additionalyRows = $additionalyRows +

                    $html .= '</tbody></table></td></tr></tbody></table>';
                    $html .= '</td><td class="no-padding no-border" style="width: 50%; padding-left: 5mm;">';
                    //$html .= '<div>Новая колонка</div>';
                    //$html .= '$additionalyRows="'.$additionalyRows.'"';
                    $html .= "\n\n" . '<table class="section-root-table"><tbody class="border-top"><tr>';
                    $html .= '<td class="w1 border-left border-right-none"><div class="section-image-box"></div></td>';
                    $html .= '<td class="no-border no-padding">';
                    $html .= '<table class="section-table"><tbody>';

                    $additionalyRows = $additionalyRows + $additionalyRows;


                } else {

                }


                if ($pageBreak) {
                    $columnBreak = false;
                    $column1Rows = 0;
                    $rowsPerColumn = 0;
                    $additionalyRows = 0;
                    /*
                    * Разрыв страницы
                    */



                    $tableName = '<div style="position:relative; height:3mm;"><div style="position:absolute; height:3mm; overflow:hidden; width:100%;">'.$ar_result['NAME'];
                    //$tableName .= ' $count="' . $count . '" ';
                    $tableName .= ' $additionalyRows="'.$additionalyRows.'" ';
                    //if ($pageBreak) $tableName .= ' ('.$additionalyRows.') ';
                    $tableName .= '</div></div>';

                    $html .= '</tbody></table></td></tr></tbody></table>';
                    $html .= '<div class="page-break-after"></div>';
                    //$html .= ' $additionalyRows="'.getRowsPerPage($additionalyRows).'" ';
                    $html .= '</td></tr></tbody></table>';
                    $html .= '<table class="root-table"><tbody class="no-border"><tr><td class="no-padding no-border" style="width: 50%;">';
                    //$html .= '<div>Начало страницы</div>';
                    $html .= "\n\n" . '<table class="section-root-table"><tbody class="border-top"><tr>';
                    $html .= '<td class="w1 border-left border-right-none"><div class="section-image-box"><img alt="" src="' . $sectionImage['src'] . '" /></div></td>';
                    $html .= '<td class="no-border no-padding">';
                    $html .= '<table class="section-table"><tbody>';
                    $html .= '<tr><td colspan="3" class="tableName">' . $tableName . '</td></tr>';
                    $html .= '<tr><td>Наименование</td><td>Опт</td><td>Розница</td></tr>';



                }

            }
        }

        //if ($pageBreak) $additionalyRows = 0;
        if ($cc == 25) break;
    }
}
$html .= '</tbody></table></td></tr></tbody></table>';
$html .= "\n" . '</td></tr></tbody></table>';

echo $html;


?>


</body>
</html>
