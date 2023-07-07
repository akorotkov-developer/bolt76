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

tr { /*border-top: 0.5mm solid #000;*/
	
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

table,td {
	padding: 0;
}

table { /*background: #000;*/
	width: 100%;
	border-collapse: collapse;
	empty-cells: show;
}

td { /*background: #fff;*/
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
	echo '';
	?>

	<?


	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
	CModule::IncludeModule("iblock");


	//echo '<h1>Прайс-лист</h1>';

	//echo (45%45).'<hr/>';

	echo (135 % 90) . '<hr/>';


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
	while ($ar_result = $db_list->GetNext()) {
		//msg($ar_result); exit;
		$hn = $ar_result['DEPTH_LEVEL'] + 1;
		if ($ar_result['RIGHT_MARGIN'] - $ar_result['LEFT_MARGIN'] == 1 /*and ($ar_result['ID']==3243)*/) {

			$toCol = '';
			$sectionsInColCounter++;
			$cc++;
			$arSelect = Array("ID", "NAME", "PROPERTY_PRICE", "PROPERTY_PRICE_OPT");
			$arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "SECTION_ID" => $ar_result['ID']);
			$res = CIBlockElement::GetList(Array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
			$count = $res->SelectedRowsCount();
			$elementsRound = round($count / 2) + 1;
			$diff = $count - $elementsRound;
			$ndiff = $elementsRound - $diff - 2;
			if ($cc == 1)
				$nce = $nce + 2;
			else {
				$nce = $nce + 3;
				$html .= '</tbody></table>';
			}

			$sectionImage = CFile::ResizeImageGet($ar_result['PICTURE'], array('width' => 100, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$html .= "\n\n" . '<table class="section-table" style="width: 100%;"><tbody>';
			$prevRowspan = 0;
			$rowspanCount = 0;
			if ($ar_result['UF_TEMPLATE'] == 0) {
				$html .= '<tr><td>Картинка</td><td>Наименование</td><td>Опт</td><td>Розница</td></tr>';
				while ($ob = $res->GetNext()) {
					//$html .= '<tr></tr>';
					$html .= '<tr><td style="text-align:center; width:1%;"><div style="width:100px;">картинка</div></td><td><div style="width:100%; position:relative; height:3mm;"><div style="height:3mm;position:absolute; width:100%; overflow:hidden; ">(' . $ce . ', ' . $nce . ') ' . $ob['NAME'] . '</div></div></td><td style="width:10%;">' . $ob['PROPERTY_PRICE_OPT_VALUE'] . '</td><td style="width:10%;">' . $ob['PROPERTY_PRICE_VALUE'] . '</td></tr>';
				}
			} else {
				$cce = 0;
				$rowspan = $count;
				$nrowspan = 45 - ($nce % 45);
				$pageBreak = false;
				if ($count > $nrowspan) {
					$rowspan = $nrowspan;
				}
				$rowspan = $rowspan + 1;
				$prevRowspan = $rowspan;
				$rowspanCount = $rowspanCount + $prevRowspan;


				$oce = 0; // количество строк в каждой таблице
				$html .= '<tr><td colspan="4">' . $ar_result['NAME'] . ' (количество элементов в таблице: ' . ($count) . ', $nrowspan="' . $nrowspan . '", $rowspan="' . $rowspan . '",)</td></tr>';
				$html .= '<tr><td rowspan="' . ($rowspan) . '" style="text-align:center; width:1%;"><div style="width:100px;"><img src="' . $sectionImage['src'] . '" alt="" /></div></td><td>Наименование</td><td>Опт</td><td>Розница</td></tr>';

				while ($ob = $res->GetNext()) {
					$oce++;
					//$nnce++;


					$cce++;

					$ce++;

					$nce++;
					$firstTr = '';

					//$nce = ($sectionsInColCounter * 3) + $nnce;


					$html .= '<tr>' . $toCol . '<td><div style="width:100%; position:relative; height:3mm;"><div style="height:3mm;position:absolute; width:100%; overflow:hidden; ">(' . $ce . ', ' . $nce . ', ' . $oce . ') ' . $ob['NAME'] . '</div></div></td><td style="width:10%;">' . $ob['PROPERTY_PRICE_OPT_VALUE'] . '</td><td style="width:10%;">' . $ob['PROPERTY_PRICE_VALUE'] . '</td></tr>';

					//if ($ce % 45 == 0 and $ce % 90 != 0) {
					$toCol = '';


					if ($nce % 90 == 45) {
						//if (false) {
						//$rowspanCount = $rowspanCount + $prevRowspan;
						/*
						* Переход в другую колонку
						*/

						//$html .= '</tbody></table></td>';
						//$html .= '<td class="no-padding no-border" style="padding-left: 5mm;">';

						/*
						 $html .= '</table></td>';
						$html .='<td class="no-padding no-border" style="padding-left: 5mm;">';
						$html .= 'переход на другую колонку '.$nce.', '.($nce % 45);
						$html .= '<table class="section-table" style="width: 100%;">';
						$firstTr = '<td rowspan="' . ($count - $oce) . '" style="width:1%;"><div style="width:100px;">' . $ce . ', ' . $nce . '</div></td>';
						*/

						//$absc = abs($count-$rowspan);
						//if ($absc>45) $rowspan = 45;
						//$rowspanCount = $absc+$rowspan;


						$partRowspan = $count - $prevRowspan + 1;
						if ($partRowspan > 45) {
							$partRowspan = 45;
							$rowspan = 45;
						}

						//$toCol = ' <td class="toCol" rowspan="' . $partRowspan . '" style="text-align:center; width:1%;"><div style="width:100px;"></div></td>';
						$prevRowspan = $rowspan + $prevRowspan;
						//$nnce = 0;
						$sectionsInColCounter = 0;

						//$html .= '</table></td><td class="no-padding no-border" style="padding-left: 5mm;"><div>Переход на другую колонку ('.$nce.', '.($nce%45).', '.($nce%90).' $partRowspan="'.$partRowspan.'" $prevRowspan="'.$prevRowspan.'")</div><table class="section-table" style="width: 100%;">';


						//$html .= 'переход на другую колонку ' . $nce . ', ' . ($nce % 90);
						//$html .= '</tbody></table><table class="section-table" style="width: 100%;"><tbody>';

					} else {
						//$prevRowspan = 0;
						//$rowspanCount = 0;
						$firstTr = '';
					}

					//if ($ce % 90 == 0) {
					//if ($nce % 90 == 0) {
					if ($nce % 90 == 0) {

						/*
						 * Разрыв страницы
						*/

						$html .= "\n" . '</tbody></table></td></tr></tbody></table><div>Конец страницы</div><hr class="page-break-after" />';
						$html .= "\n\n" . '<table class="root-table"><tbody class="no-border"><tr><td class="no-padding no-border" style="width: 50%;">';
						$html .= '<div>Начало новой страницы</div>';

						//$html .= '</table></td></tr></tbody></table><div class="page-break-after"></div>';
						//$html .= '<table class="root-table"><tbody class="no-border"><tr><td class="no-padding no-border" style="width: 50%;">';

						if ($oce != $count) {
							$html .= '<table class="section-table" style="width: 100%;">';
							$html .= '<tr><td colspan="4">' . $ar_result['NAME'] . '</td></tr>';
							$html .= '<tr><td rowspan="' . (($count - $cce) + 1) . '" style="width:1%; text-align:center;"><div style="width:100px;"><!--' . $sectionsInColCounter . '--><img src="' . $sectionImage['src'] . '" alt="" /></div></td><td> Наименование</td><td>Опт</td><td>Розница</td></tr>';
						}
						$cce = 0;
						//$nnce=0;
						$sectionsInColCounter = 0;
					}

				}
			}
			/*
			 if ($oce == $count)
				$html .= '</table>';
			*/
			//msg($cc);
			//$rowspanCount = 0;
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
