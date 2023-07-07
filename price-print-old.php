<!doctype html>
<html>
<head>
    <title>Прайс-лист</title>
    <style type="text/css" media="all">

        body{

            font-family: Arial, sans-serif;
            font-size: 8pt;
        }
        .page-break-after {
            page-break-after: always;
        }
        .page-break-before {
            page-break-before: always;
        }
        td{
            vertical-align: top;
            border-bottom: 0.5mm solid #000;
            border-right: 0.5mm solid #000;
        }
        td.first{
            border-top: 0.5mm solid #000;

        }
        td.image{
            border-left: 0.5mm solid #000;
        }
        tbody{
            border-bottom: 0.5mm solid #000;
            border-left: 0.5mm solid #000;
        }
        tr{
            /*border-top: 0.5mm solid #000;*/
        }
        .border-left{
            border-left: 0.5mm solid #000;
        }
        .border-right{
            border-right: 0.5mm solid #000;
        }
        .border-top{
            border-top: 0.5mm solid #000;
        }
        table.parent{
            border: none;
            margin-bottom: 1cm;
            border-collapse: collapse;
        }
        table.parent tbody{
            border: none;
        }
        tr.parent{
            border: none;
            padding: 0
        }
        td.parent{
            border: none;
            padding: 0;
        }

        table, td{
            padding: 0;

        }
        table{
            /*background: #000;*/
            border-collapse: collapse;
            empty-cells: show;
        }
        td{
            /*background: #fff;*/
            padding: 1mm;
        }
        .no-border{
            border: none;
        }
    </style>
</head>
<body>


<?


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");


//echo '<h1>Прайс-лист</h1>';


$arFilter = array('IBLOCK_ID' => 1, "ACTIVE" => "Y");
$db_list = CIBlockSection::GetList(Array("left_margin" => "asc"), $arFilter, true, array('UF_*'));


$cc = 0;

while ($ar_result = $db_list->GetNext()) {
    //msg($ar_result); exit;
    $hn = $ar_result['DEPTH_LEVEL'] + 1;
    //echo '<h' . $hn . '>' . $ar_result['NAME'] . '</h' . $hn . '>';
    if ($ar_result['RIGHT_MARGIN'] - $ar_result['LEFT_MARGIN'] == 1) {
        $cc++;
        $arSelect = Array("ID", "NAME");
        $arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "SECTION_ID" => $ar_result['ID']);
        $res = CIBlockElement::GetList(Array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
        $count = $res->SelectedRowsCount(); // количество полученных записей из таблицы
        $elementsRound = round($count / 2) + 1;
        $diff = $count - $elementsRound;
        $ndiff = $elementsRound - $diff - 2;
        //msg($ar_result['UF_TEMPLATE']);

        $sectionImage = CFile::GetPath($ar_result['PICTURE']);
        if ($ar_result['UF_TEMPLATE'] == 0) {
            $html = 'Шаблон вывода с картинками';
            $html .= '<table width="100%">';
            while ($ob = $res->GetNext()) {
                //msg($ob);
                $html .= '<tr><td>' . $ob['NAME'] . '</td></tr>';
            }
            $html .= '</table>';
        } else {
            $ce = 0;
            $col1 = '';
            $col2 = '';
            while ($ob = $res->GetNext()) {
                $ce++;
                if ($ce < $elementsRound) {
                    $fcol = '';
                    $f = '';
                    if ($ce == 1) {
                        $f = ' class="first border-left"';
                        $fcol = '<td class="first image" rowspan="' . $elementsRound . '">картинка раздела</td>';
                    }
                    $col1 .= '<tr>' . $fcol . '<td'.$f.'>' . $ob['NAME'] . '</td><td>Опт</td><td>Розница</td></tr>';
                } else {
                    $fcol = '';
                    $f = '';
                    if ($ce == ($elementsRound)) {
                        $f = ' class="first"';
                        //$fcol = '<td class="first" rowspan="'.($diff+1+$ndiff).'"><!-- не показываем картинку раздела во второй колонке --></td>';
                    }
                    $col2 .= '<tr>'.$fcol.'<td'.$f.'>' . $ob['NAME'] . '</td><td>Опт</td><td>Розница</td></tr>';

                }
            }

            //$col2 .= str_repeat('<tr><td></td></tr>', $ndiff);
        }

        $html = '<table class="parent">';
        $html .= '<tr><td colspan="7" style="border:none; padding:0;" class=""><div style="padding:0.5em;" class="border-top border-right border-left">'.$ar_result['NAME'].'</div></td></tr>';
        $html .= '<tr class="parent"><td class="parent"><table><tbody><tr><td class="border-top border-left">Изображение</td><td class="border-top">Наименование</td><td class="border-top">Опт</td><td class="border-top">Розница</td></tr>' . $col1 . '</tbody></table></td><td class="parent"><table><tbody><tr><td class="border-top">Наименование</td><td class="border-top">Опт</td><td class="border-top">Розница</td></tr>' . $col2 . '</tbody></table></td></tr>';
        $html .= '</table>';
        echo $html;
        if ($cc == 6) exit;
    }
    if ($ar_result['DEPTH_LEVEL']<3 and $cc>2) {
        echo '<hr class="page-break-after" />';
    }
}




?>


</body>
</html>