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
 </b></span>Пн-Пт: &nbsp;9:00 - 17:00<br>
 <br>
 <span style="color: #ff7920;"><b>Магазин,&nbsp;склад:<br>
 </b></span>Пн-Пт: &nbsp;8:30 - 18:00&nbsp;<br>
		 Сб: 10:00 - 16:00<br>
		 Вс:&nbsp;<span style="color: #ff0000;"><b>Выходной<br>
 </b></span><br>
		 <!--		<h3 style="color: #ff7920;">График работы в праздничные дни:</h3>
		 12 июня:&nbsp;<span style="color: #ff0000;"><b>Выходной<br> -->
		<h3>Свяжитесь с нами:</h3>
		<table width="100%" cellpadding="2" cellspacing="1">
		<tbody>
		<tr>
			<td rowspan="1">
 <img alt="Иконка телефона" src="/upload/medialibrary/6f2/6f25717e1468b621dc51261ea8c1801e.png" title="Иконка телефона" align="left" class="icons_img">
				<p>
					 +7 (4852) 58-04-45<br>
					 +7 (4852) 58-04-46
				</p>
			</td>
			<td rowspan="1">
 <img alt="mail_FILL0_wght400_GRAD0_opsz48.png" src="/upload/medialibrary/80f/80f61c870fb7d147cf80360429c38c1d.png" title="Иконка почты" align="left" class="icons_img">
				<p>
 <a title="Напишите нам" href="mailto:mail@strprofi.ru">mail@strprofi.ru</a>
				</p>
			</td>
		</tr>
		</tbody>
		</table>
		<h3>Реквизиты:</h3>
		 ИП Богинский Василий Владимирович.<br>
		 ИНН 760200472350 / ОГРНИП 308760221700018.<br>
		 Адрес: 150044, г.Ярославль, Ленинградский пр-т. 33, ТЦ "Омега"<br>
		 Магазин - 1-й этаж модуль №109<br>
		 Офис - 5-й этаж №501<br>
		 Тел./факс: +7 (4852) 58-04-45, +7 (4852) 58-04-46<br>
	</td>
	<td rowspan="6">
 <img src="/upload/medialibrary/beb/bebb7ac1ad9d540330921213ae91751a.jpeg" alt="ОМЕГА" title="ОМЕГА" border="0" class="table_big_img"> <br>
 <img src="/upload/medialibrary/ffd/ffd47fe1ef53ee52b8a699b56699cf01.jpg" title="Крепеж" alt="Крепеж" class="table_big_img">
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