<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Контакты");
?><table width="70%" cellspacing="1" align="center" class="border-zero">
<tbody>
<tr>
	<td colspan="6">
		<h3> Наш адрес:</h3>
		 150044, г.Ярославль, Ленинградский пр-т. 33,<br>
		 ТЦ "Омега"<br>
		<h3>Режим работы:</h3>
 <span style="color: #ff7920;"><b>Офис:<br>
		 №501 5-й этаж</b></span><span style="color: #ff7920;"><b><br>
 </b></span>Пн-Пт: &nbsp;9:00 - 17:00<br>
 <br>
 <span style="color: #ff7920;"><b>Магазин,&nbsp;склад:<br>
		 №109 1-й этаж</b></span><span style="color: #ff7920;"><b><br>
 </b></span>Пн-Пт: &nbsp;8:30 - 18:00&nbsp;<br>
		 Сб: <span style="color: #ff0000;"><b>Выходной (временно)</b></span><br>
		 Вс:&nbsp;<span style="color: #ff0000;"><b>Выходной<br>
 </b></span><br>
		 <!-- <h3 style="color: #ff7920;">График работы магазина в праздничные дни:</h3>
		<table cellpadding="3" cellspacing="3">
		<tbody>
		<tr>
			<td>
				 29 декабря - 2 января
			</td>
			<td>
 <span style="color: #ff0000;"><b>Выходной<br>
 </b></span>
			</td>
		</tr>
		<tr>
			<td colspan="1">
				 3 - 4 января
			</td>
			<td colspan="1">
				<div>
					 10:00 - 16:00
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="1">
				 5 января&nbsp;
			</td>
			<td colspan="1">
 <b style="color: #ff0000;">Выходной</b>
			</td>
		</tr>
		<tr>
			<td colspan="1">
				 6 января&nbsp;
			</td>
			<td colspan="1">
				 10:00 - 16:00
			</td>
		</tr>
		<tr>
			<td colspan="1">
				 7 января<br>
			</td>
			<td colspan="1">
 <b style="color: #ff0000;">Выходной</b>
			</td>
		</tr>
		<tr>
			<td colspan="1">
				 8 января&nbsp;
			</td>
			<td colspan="1">
				<div>
					 10:00 - 16:00
				</div>
			</td>
		</tr>
		</tbody>
		</table> -->
		<h3>Свяжитесь с нами:</h3>
		<table width="100%" cellpadding="2" cellspacing="1">
		<tbody>
		<tr>
			<td rowspan="1">
 <img alt="Иконка телефона" src="/upload/medialibrary/6f2/6f25717e1468b621dc51261ea8c1801e.png" title="Иконка телефона" align="left" class="icons_img">
				<p>
					 +7 (4852) 93 63 53<br>
				</p>
			</td>
			<td rowspan="1">
 <img alt="mail_FILL0_wght400_GRAD0_opsz48.png" src="/upload/medialibrary/80f/80f61c870fb7d147cf80360429c38c1d.png" title="Иконка почты" align="left" class="icons_img">
				<p>
 <a title="Напишите нам" href="mailto:mail@strprofi.ru">mail@strprofi.ru</a>
				</p>
			</td>
		</tr>
		<tr>
			<td rowspan="1" colspan="2">
 <img alt="whatsappLogo" src="/upload/medialibrary/0ea/0ea5242001338471d2ca83664dee1553.png" title="whatsappLogo" align="left" class="icons_img">
				<p>
					 +7 (951) 283 63 53<br>
				</p>
				 &nbsp;
			</td>
		</tr>
		</tbody>
		</table>
		<h3>Реквизиты:</h3>
		 ИП Богинский Василий Владимирович.<br>
		 ИНН 760200472350 / ОГРНИП 308760221700018.<br>
		 Фактический адрес: 150044, г.Ярославль, Ленинградский пр-т. 33-501, ТЦ "Омега"
	</td>
	<td rowspan="6">
 <img src="/upload/medialibrary/beb/bebb7ac1ad9d540330921213ae91751a.jpeg" alt="ОМЕГА" title="ОМЕГА" border="0" class="table_big_img"> <br>
 <img src="/upload/medialibrary/ffd/ffd47fe1ef53ee52b8a699b56699cf01.jpg" title="Крепеж" alt="Крепеж" class="table_big_img"><br>
 <img src="<?= SITE_TEMPLATE_PATH?>/images/gpsqr.png" title="Крепеж" alt="Крепеж" class="table_big_img">
	</td>
</tr>
</tbody>
</table>
 <br>
 <br>
 <?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	".default",
	Array(
		"CONTROLS" => array(0=>"SMALLZOOM",),
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:57.67395798167891;s:10:\"yandex_lon\";d:39.819807919883985;s:12:\"yandex_scale\";i:15;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:3:\"LON\";d:39.8210739225375;s:3:\"LAT\";d:57.67371655701367;s:4:\"TEXT\";s:0:\"\";}}}",
		"MAP_HEIGHT" => "500",
		"MAP_ID" => "",
		"MAP_WIDTH" => "100%",
		"OPTIONS" => array(0=>"ENABLE_SCROLL_ZOOM",1=>"ENABLE_DBLCLICK_ZOOM",2=>"ENABLE_DRAGGING",)
	)
);?> <br>
 <br><? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>