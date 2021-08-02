<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поиск по сайту");
CModule::IncludeModule("iblock");
?><div class="search_results">
    <form action="/search/" method="get">
        <input type="text" name="q" value="<?=htmlspecialchars($_GET["q"]);?>" class="search-input">
        <input type="submit" value="" class="search-submit">
		<?
		$where = ($_GET['where'] == "articul")?"articul":"name";
		?>
        <div class="clear"></div>
    </form>
    <br>
	<?
	$where = ($_GET['where'] == "articul") ? "articul" : "name";

	$q = trim(strip_tags($_GET["q"]));
	if(mb_strlen($q)>=1){
		$finded = false;
		$q = str_replace(Array(" ", " ", "-", "_", "\"", "/", "\\", "(", ")"), "%", $q);
		$q = "%".$q."%";
		if ($where == "name") {
			$arFilter = Array('IBLOCK_ID' => 1, 'NAME' => $q);
			$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_PRODUCT", "UF_PRICE_FROM"));
			$db_list->NavStart(25);
			$selectedCount = min($db_list->SelectedRowsCount(),25);
			if($selectedCount>0) {?>
                <table class="full">
					<?  $i=0;
					while ($ar_result = $db_list->GetNext()) {
						$i++;
						$finded = true;?>
                        <tr<?=($selectedCount==$i?' class="last"':'')?>>
                            <td class="picture"><?
								if($ar_result["PICTURE"]>0){
									$file = $ar_result["PICTURE"];
								}else{
									$file = false;
								}

								if($file){
									$file = CFile::ResizeImageGet($file, array('width' => 50, 'height' => 50), BX_RESIZE_IMAGE_PROPORTIONAL, true);
									?>
                                    <a href="<?=$ar_result["SECTION_PAGE_URL"]?>"><img src="<?=$file["src"]?>" alt="<?=$ar_result["NAME"]?>"></a>
									<?}?></td>
                            <td class="product">
                                <div class="section_name">
                                    <a href="<?=$ar_result["SECTION_PAGE_URL"]?>"><?=$ar_result["NAME"]?></a>
                                </div>
                                <div class="price">
                                    от <span><?=$ar_result["UF_PRICE_FROM"]?></span> руб
                                </div>
                            </td>
                        </tr>
						<?}?></table><?
			}
		}
		$arSelect = Array("ID", "NAME", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", "PROPERTY_PRICE_OPT", "PROPERTY_PRICE");
		$arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y");
        $arFilter = [
            'LOGIC' => 'OR',
            'PROPERTY_ARTICUL' => str_replace("%", "", $q),
            'NAME' => $q
        ];
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>250), $arSelect);
		$arElements = Array();
		$sections = Array();
		while ($ob = $res->GetNext()) {
			$arElements[] = $ob;
			$sections[] = $ob["IBLOCK_SECTION_ID"];
		}

		$arFilter = Array('IBLOCK_ID' =>1, 'ID' => $sections);
		$db_list = CIBlockSection::GetList(Array(), $arFilter, false);
		while ($ar_result = $db_list->GetNext()) {
			$sectionInfo[$ar_result["ID"]] = Array("NAME"=>$ar_result["NAME"], "SECTION_PAGE_URL"=>$ar_result["SECTION_PAGE_URL"], "PICTURE"=>$ar_result["PICTURE"]);
		}

		if(sizeof($arElements)>0){?>
            <hr class="dashed">
            <table class="full"><?
				foreach($arElements as $key=>$arElement){$finded=true;?>
                    <tr<?=(($key+1)==sizeof($arElements)?' class="last"':'')?>>
                        <td class="picture"><?
							if($arElement["PREVIEW_PICTURE"]>0){
								$file = $arElement["PREVIEW_PICTURE"];
							}elseif($sectionInfo[$arElement["IBLOCK_SECTION_ID"]]["PICTURE"]>0){
								$file = $sectionInfo[$arElement["IBLOCK_SECTION_ID"]]["PICTURE"];
							}else{
								$file = false;
							}

							if($file){
								$file = CFile::ResizeImageGet($file, array('width' => 50, 'height' => 50), BX_RESIZE_IMAGE_PROPORTIONAL, true);
								?>
                                <a href="<?=$sectionInfo[$arElement["IBLOCK_SECTION_ID"]]["SECTION_PAGE_URL"]?>"><img src="<?=$file["src"]?>" alt="<?=$arElement["NAME"]?>"></a>
								<?}?></td>
                        <td class="product">
                            <div class="section_name">
                                <a href="<?=$sectionInfo[$arElement["IBLOCK_SECTION_ID"]]["SECTION_PAGE_URL"]?>"><?=$arElement["NAME"]?></a>
                            </div>
                            <div class="name">
                                <a href="<?=$sectionInfo[$arElement["IBLOCK_SECTION_ID"]]["SECTION_PAGE_URL"]?>"><?=$sectionInfo[$arElement["IBLOCK_SECTION_ID"]]["NAME"]?></a>
                            </div>
							<?if($arElement["PROPERTY_PRICE_OPT_VALUE"]>0){?>
                            <div class="price">
                                от <span><?=($arElement["PROPERTY_PRICE_OPT_VALUE"]?$arElement["PROPERTY_PRICE_OPT_VALUE"]:$arElement["PROPERTY_PRICE_VALUE"])?></span> руб
                            </div>
							<?}?>
                        </td>
                    </tr>
					<?}?>
            </table>
			<?}

		if(!$finded){?>Ничего не найдено<?}
	}
	?>

</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>