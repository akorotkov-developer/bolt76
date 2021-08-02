<?
echo '<div class="catalog_element">';
if ($_GET["clear"] == "Y") {
	setcookie("cart", "", time() - 10, "/");
	header("Location: /cart/");
}

if ($_GET["order"] == "ok") {
	echo $arParams['~THANKS'];
} else {
	?>
	<form action="/cart/add_to_cart.php">
		Быстрое добавление по артикулу:
		<input type="text" name="articul" value="" style="width: 100px;"/>
		<input type="submit" value="Добавить"/>
	</form><br/><?
	$cartInfo = json_decode($_COOKIE['cart'], true);
	$elements = $cartInfo["ELEMENTS"];
	$error = '';
	if (isset($_POST["ITEM"])) {

		$newCartInfo = Array();
		foreach ($_POST["ITEM"] as $id => $count) {
			if ((int)$count > 0) {
				$newCartInfo["ELEMENTS"][$id] += $count;
			}
		}
		$price = 0;
		$count = 0;


		if (sizeof($newCartInfo["ELEMENTS"]) > 0) {
			$arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRICE");
			$arFilter = Array("IBLOCK_ID" => 1, "ID" => array_keys($newCartInfo["ELEMENTS"]));
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while ($ob = $res->GetNext()) {
				$price += $ob["PROPERTY_PRICE_VALUE"] * (int)$newCartInfo["ELEMENTS"][$ob["ID"]];
				$count += $newCartInfo["ELEMENTS"][$ob["ID"]];
			}
			$newCartInfo["PRICE"] = $price;
			$newCartInfo["COUNT"] = $count;


			setcookie("cart", json_encode($newCartInfo), strtotime("+1 month"), "/");
		}

		$price = 0;
		$count = 0;
		$elements = $_POST["ITEM"];

		if ($_POST["recount_cart"] != "Y") {


			$_POST["email"] = strip_tags(htmlspecialchars($_POST["email"]));
			$mobile = preg_replace('/[^0-9]/', '', $_POST["phone"]);
			if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
				$error .= '<p>Ошибка: неправильный Email</p>';
			}

			if (mb_strlen($mobile) < 6) {
				$error .= '<p>Ошибка: укажите существущий номер телефона для связи</p>';
			}

			if (mb_strlen($_POST["fio"]) < 3) {
				$error .= '<p>Ошибка: укажите, пожалуйста, Ваше имя.</p>';
			}

			if (trim($_POST["fio"]) != '' && $error == '') {
				$arSelect = Array("ID", "NAME", "PROPERTY_ARTICUL", "PROPERTY_PRICE", "PROPERTY_NOMNOMER", "PROPERTY_NAIMENOVANIE", "PROPERTY_PRICE_OPT", "IBLOCK_ID", "PROPERTY_UNITS");
				$arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "ID" => array_keys($elements));
				$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				$obs = Array();
				while ($ob = $res->GetNext()) {
					$obs[$ob['ID']] = $ob;
				}
				$styles = Array(
					"td" => "padding: 3px 5px 3px 5px; border: solid 1px #E0E0E0;",
					"td20" => "width:20px;",
					"td100" => "width:100px;",
					"tr0" => "background: #ffffff;",
					"tr1" => "background: #F0F0F0;"
				);
				$text = '<table style="border-spacing:0px;border-collapse:collapse;width:100%;font-size:12px;font-family:Arial;">
					<tr style="background:#FF7920;">
					<td style="'.$styles["td"].$styles["td20"].'">№</td>
					<td style="'.$styles["td"].$styles["td100"].'">Артикул</td>
					<td style="'.$styles["td"].'">Наименование</td>
					<td style="'.$styles["td"].$styles["td100"].'">Количество</td>
					<td style="'.$styles["td"].$styles["td100"].'">Цена роз</td>
					<td style="'.$styles["td"].$styles["td100"].'">Цена опт</td>
					</tr>';
				$i = 0;
				$orderID = (int)file_get_contents("lastOrder.txt");
				$orderID++;
				$XML_time = date("Y-m-d")."T".date("H:i:s");
				if (true) {
					$XML = '<?xml version="1.0" encoding="Windows-1251"?>
<ФайлОбмена ВерсияФормата="1.0" ДатаВыгрузки="'.$XML_time.'" Комментарий="">
    <ПравилаОбмена/>
    <Объект Нпп="1" Тип="СправочникСсылка.Лица">
        <Свойство Имя="ЮрФизЛицо" Тип="Строка">
            <Значение>Организация</Значение>
        </Свойство>
        <Свойство Имя="ИНН" Тип="Строка">
            <Значение>ЗАКАЗ</Значение>
        </Свойство>
        <Свойство Имя="КПП" Тип="Строка">
            <Пусто/>
        </Свойство>
        <Свойство Имя="КодПоОКПО" Тип="Строка">
            <Пусто/>
        </Свойство>
        <Свойство Имя="Родитель" Тип="Строка">
            <Значение>Частные лица</Значение>
        </Свойство>
        <Свойство Имя="НаименованиеПолное" Тип="Строка">
            <Значение>ЗАКАЗ С САЙТА</Значение>
        </Свойство>
        <Свойство Имя="Наименование" Тип="Строка">
            <Значение>Центральный склад</Значение>
        </Свойство>
        <Свойство Имя="Комментарий" Тип="Строка">
            <Пусто/>
        </Свойство>
    </Объект>
    <Объект Нпп="2" Тип="Исходящие счета">
        <Свойство Имя="Дата" Тип="Дата">
            <Значение>'.$XML_time.'</Значение>
        </Свойство>
        <Свойство Имя="ВалютаДокумента" Тип="Строка">
            <Пусто/>
        </Свойство>
        <Свойство Имя="КурсВзаиморасчетов" Тип="Число">
            <Пусто/>
        </Свойство>
        <Свойство Имя="Контрагент" Тип="СправочникСсылка.Лица">
            <Нпп>1</Нпп>
        </Свойство>
        <Свойство Имя="Склад" Тип="Число">
            <Значение>9</Значение>
        </Свойство>
        <Свойство Имя="СуммаДокумента" Тип="Число">
            <Значение>0</Значение>
        </Свойство>
        <Свойство Имя="Комментарий" Тип="Строка">
            <Значение>Заказ '.$orderID.', Имя: '.$_POST["fio"].', Телефон: '.$_POST["phone"].'</Значение>
        </Свойство>
        <Свойство Имя="Номер" Тип="Строка">
            <Пусто/>
        </Свойство>
    </Объект>
';
				}
				$j = 1;
				$n = 3;
				foreach($elements as $k=>$v) {
					$ob = $obs[$k];
					$text .= '<tr style="'.$styles["tr".($i++%2)].'">
						<td style="'.$styles["td"].$styles["td20"].'">' . ($j++) . '.</td>
						<td style="'.$styles["td"].$styles["td100"].'">' . $ob["PROPERTY_ARTICUL_VALUE"] . '</td>
						<td style="'.$styles["td"].'">' . $ob["PROPERTY_NAIMENOVANIE_VALUE"] . '</td>
						<td style="'.$styles["td"].$styles["td100"].'">' . $elements[$ob["ID"]] . '</td>
						<td style="'.$styles["td"].$styles["td100"].'">' . $ob["PROPERTY_PRICE_VALUE"] . ' руб</td>
						<td style="'.$styles["td"].$styles["td100"].'">' . $ob["PROPERTY_PRICE_OPT_VALUE"] . ' руб</td>
						</tr>';
					$XML .= '<Объект Нпп="'.($n).'" Тип="СправочникСсылка.Складская картотека">
        <Свойство Имя="Склад реализации/именного списания" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="Учет по продажной цене" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="Склад хранения" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="Cклад з. путевых листов" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="Учет по местам хранения" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="Учет по видам собственности" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="Склад" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="Группа складов" Тип="Булево"><Значение>false</Значение></Свойство>
        <Свойство Имя="НаименованиеПолное" Тип="Строка"><Значение>'.$ob["PROPERTY_NAIMENOVANIE_VALUE"].'</Значение></Свойство>
        <Свойство Имя="БазоваяЕдиницаИзмерения" Тип="Строка"><Значение>'.$ob["PROPERTY_UNITS_VALUE"].'</Значение></Свойство>
        <Свойство Имя="Код" Тип="Строка"><Значение>'.$ob["PROPERTY_NOMNOMER_VALUE"].'</Значение></Свойство>
        <Свойство Имя="Комментарий" Тип="Строка"><Пусто/></Свойство>
        <Свойство Имя="Наименование" Тип="Строка"><Значение>'.$ob["NAME"].'</Значение></Свойство>
    </Объект>
    <Объект Нпп="'.($n+1).'" Тип="СправочникСсылка.Наименования счета">
        <Свойство Имя="ДокументНаименования" Тип="Исходящие счета"><Нпп>2</Нпп></Свойство>
        <Свойство Имя="Количество" Тип="Число"><Значение>'.$elements[$ob["ID"]].'</Значение></Свойство>
        <Свойство Имя="Номенклатура" Тип="СправочникСсылка.Складская картотека"><Нпп>'.($n).'</Нпп></Свойство>
        <Свойство Имя="СуммаНДС" Тип="Число"><Пусто/></Свойство>
        <Свойство Имя="Сумма" Тип="Число"><Значение>0</Значение></Свойство>
        <Свойство Имя="Цена" Тип="Число"><Значение>0</Значение></Свойство>
    </Объект>';
					$n += 2;
				}
				$XML .= '</ФайлОбмена>';
				$XML = iconv("UTF-8", "Windows-1251", $XML);

				$text .= '</table>';
				$items_list = $text;

				$text = "";
				$text .= "<h3 style='padding:5px;color:#000;margin-bottom:10px;'>Здравствуйте!</h3>";
				$text .= "<p style='padding: 5px;'>".date("d.m.Y H:i")." на сайте разместили заказ № ".$orderID."<br/>Его состав:</p>";
				$text .= $items_list;
				$text .= "<p><b>Указанные данные:</b></p>";
				$text .= "ФИО: " . $_POST["fio"] . "<br/>";
				$text .= "Email: " . $_POST["email"]. "<br/>";
				$text .= "Телефон: " . $_POST["phone"]. "";
				if ($_POST["comment"]) $text .= "<br/>Комментарий к заказу:<br/>" . $_POST["comment"]. "";

				//добавляем элемент
				file_put_contents("Исходящие счета.xml", $XML);

				$el = new CIBlockElement;
				$PROP = array(
					21 => CFile::MakeFileArray("https://strprofi.ru/cart/Исходящие счета.xml")
				);

				$arLoadProductArray = Array(
					"IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
					"IBLOCK_ID"      => 6,
					"PROPERTY_VALUES"=> $PROP,
					"NAME"           => "Заказ #".$orderID,
					"ACTIVE"         => "Y",            // активен
					"PREVIEW_TEXT"   => "",
					"DETAIL_TEXT"    => $text,
					"DETAIL_TEXT_TYPE"    => "html"
				);
				$el->Add($arLoadProductArray);

				// pismo("mail@strprofi.ru, strprofi@yandex.ru", "Новый заказ. № ".$orderID." от ".date("d.m.Y"), $text, "mail@strprofi.ru", "", "", $XML, "Исходящие счета.xml");

				$text = "";
				$text .= "<p style='padding:5px;background: #FFE5D4;color:#000;margin-bottom:10px;'>Информационное сообщение с сайта www.strprofi.ru</p>";
				$text .= "<h3 style='padding:5px;color:#000;margin-bottom:10px;'>Здравствуйте, ".$_POST["fio"]."!</h3>";
				$text .= "<p style='padding: 5px;'>Вы разместили заказ № ".$orderID." от ".date("d.m.Y H:i").".<br/>Его состав:</p>";
				$text .= $items_list;
				$text .= "<p><b>Указанные вами данные:</b></p>";
				$text .= "ФИО: " . $_POST["fio"] . "<br/>";
				$text .= "Email: " . $_POST["email"]. "<br/>";
				$text .= "Телефон: " . $_POST["phone"];
				if ($_POST["comment"]) $text .= "<br/>Комментарий к заказу:<br/>" . $_POST["comment"]. "";
				$text .= "<p style='padding:5px;background: #FFE5D4;color:#000;margin-bottom:10px;'>В ближайшее время мы свяжемся с Вами. Если у вас остались какие-то вопросы, то свяжитесь с нами по телефону <strong>(4852) 58-04-45</strong></p>";

				pismo($_POST["email"], "Заказ № ".$orderID." от ".date("d.m.Y"), $text);
				pismo("mail@strprofi.ru", "Заказ № ".$orderID." от ".date("d.m.Y"), $text, $from = "mail@strprofi.ru", $ReplyTo = "", $fromName = "", $_SERVER['DOCUMENT_ROOT'] . '/cart/Исходящие счета.xml');
				pismo("strprofi@yandex.ru", "Заказ № ".$orderID." от ".date("d.m.Y"), $text, $from = "mail@strprofi.ru", $ReplyTo = "", $fromName = "", $_SERVER['DOCUMENT_ROOT'] . '/cart/Исходящие счета.xml');

                unlink("Исходящие счета.xml");

				setcookie("cart", "", time() - 10, "/");

				$f = fopen("lastOrder.txt", "w");
				fputs($f, $orderID);
				fclose($f);
			} else {
				echo '<div class="error">'.$error.'</div>';
			}
		} else {
			$error = 'e';
		}
	}

	if ($error != '' || (!isset($_POST["ITEM"]) && $error == '')) {
		if (sizeof($elements) > 0 && is_array($elements)) { ?>
			<form action="/cart/" method="post">
				<table class="full element_table">
					<thead>
					<tr>
						<td style="width:20px;">№</td>
						<td class="art">Арт</td>
						<td class="name">Наименование</td>
						<td class="opt">Опт</td>
						<td class="roz">Розница</td>
						<td class="buy">Купить</td>
						<td class="mera">Ед</td>
						<td class="delete"></td>
					</tr>
					</thead>
					<tbody>
					<?
					$arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_*");
					$arFilter = Array("IBLOCK_ID" => 1, "ID" => array_keys($elements));
					$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
					$n = 1;
					while ($ob = $res->GetNextElement()) {
						$arElement = $ob->GetFields();
						$arElement["PROPERTIES"] = $ob->GetProperties();
						?>
						<tr>
							<td><?=$n++;?>.</td>
							<td class="art"><?=$arElement["PROPERTIES"]["ARTICUL"]["VALUE"]?></td>
							<td class="name">
								<div class="name_wrapper">
									<div class="name-holder">
										<?= $arElement["PROPERTIES"]['NAIMENOVANIE']['VALUE'] ?>
									</div>
								</div>
							</td>
							<td class="opt"><?=coolPrice($arElement["PROPERTIES"]["PRICE_OPT"]["VALUE"])?></td>
							<td class="roz"><?=coolPrice($arElement["PROPERTIES"]["PRICE"]["VALUE"])?></td>
							<td class="buy">
								<div class="buy_helper_holder">
									<div class="buy_helper">
										<div class="input_holder"><input type="text"
										                                 name="ITEM[<?=$arElement["ID"]?>]"
										                                 value="<?=$elements[$arElement["ID"]]?>"
										                                 data-price="<?=$arElement["PROPERTIES"]["PRICE"]["VALUE"]?>">
										</div>
									</div>
								</div>
							</td>
							<td class="mera"><?=$arElement["PROPERTIES"]["UNITS"]["VALUE"]?></td>
							<td class="delete"><a href="#" class="link_like_button"
							                      data-id="<?=$arElement["ID"]?>">&times;</a></td>
						</tr>
					<? }?>
					</tbody>
				</table>
				<div class="order">
					<a href="/cart/?clear=Y" class="link_like_button clear_cart"
					   onclick="return confirm('Точно очистить?');">Очистить корзину</a>
					<a href="#" class="link_like_button recount_cart">Пересчитать</a>
					<input type="hidden" value="N" name="recount_cart" id="recount_cart">

					<div class="order_precount"> <?=$cartInfo["COUNT"]?> товаров на <?=$cartInfo["PRICE"]?>руб
					</div>
					<div class="clear"></div>
				</div>
				<table class="order_form">
					<tr>
						<td class="name">Ваше имя*:</td>
						<td class="value"><input type="text" name="fio" value="<?=$_POST["fio"]?>"/></td>
					</tr>
					<tr>
						<td class="name">Ваш телефон*:</td>
						<td class="value"><input type="text" name="phone" value="<?=$_POST["phone"]?>"/></td>
					</tr>
					<tr>
						<td class="name">Ваш email*:</td>
						<td class="value"><input type="text" name="email" value="<?=$_POST["email"]?>"/></td>
					</tr>
					<tr>
						<td class="name">Комментарий</td>
						<td class="value"><textarea name="comment" id="" cols="30" rows="10"></textarea></td>
					</tr>
					<tr>
						<td class="name"><br><a href="#" class="link_like_button"
						                        onclick="$(this).closest('form').submit();">Оформить заказ</a></td>
						<td></td>
					</tr>
				</table>
			</form>
		<? } else {
			echo $arParams['~EMPTY'];
		}
	} else {
		header("Location: /cart/?order=ok");
	}
}
echo '</div>';
?>
<!-- w --> <!-- form.php -->