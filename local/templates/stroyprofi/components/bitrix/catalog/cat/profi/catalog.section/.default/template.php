<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(sizeof($arResult["ITEMS"])>0){
	?>
<div class="catalog_element">
    <form action="/cart/add_to_cart.php" method="post" class="order_form">
        <table class="full element_table">
            <thead>
            <tr>
                <td class="pic"></td>
                <td class="art">Арт</td>
                <td class="nopadding-i"></td>
                <td class="name">Наименование</td>
                <td class="opt">Опт</td>
                <td class="roz">Розница</td>
                <td class="upak">В упаковке</td>
                <td class="avail">Наличие</td>
                <td class="buy">Купить</td>
                <td class="mera">Ед</td>
                <td></td>
            </tr>
            </thead>
            <tbody>
				<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
				<?
				$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
	            $up1 = (float)$arElement["PROPERTIES"]["UPAKOVKA"]["VALUE"];
	            $up2 = (float)$arElement["PROPERTIES"]["UPAKOVKA2"]["VALUE"];
	            $countTips = Array();
	            if ($up1 && !$up2) {
		            $countTips[] = $up1;
		            $countTips[] = $up1*10;
		            $countTips[] = $up1*100;
	            } elseif ($up2 < $up1) {
		            $countTips[] = $up2;
		            $countTips[] = $up1;
	            } elseif (($up1 < $up2) && ($up2 <= 10*$up1)) {
		            $countTips[] = $up1;
		            $countTips[] = $up2;
	            } elseif (($up1 < $up2) && ($up2 >= 10*$up1) && ($up2 <= 100*$up1)) {
		            $countTips[] = $up1;
		            $countTips[] = $up1*10;
		            $countTips[] = $up2;
	            } elseif (($up1 < $up2) && ($up2 >= 100*$up1)) {
		            $countTips[] = $up1;
		            $countTips[] = $up1*10;
		            $countTips[] = $up1*100;
		            $countTips[] = $up2;
	            }
	            $arElement["NAME"] = ($arElement["NAME"]=="-"?$arElement["PROPERTIES"]["NAIMENOVANIE"]["VALUE"]:$arElement["NAME"]);
				?>
            <tr id="<?=$this->GetEditAreaId($arElement['ID']);?>" class="<?=((float)$arElement["PROPERTIES"]["OSTATOK"]["VALUE"]>0?'available':'not-available')?> row<?=($cell%2);?>">
                <td class="pic no-back-hover">
					<?if($arElement["PREVIEW_PICTURE"]){?>
					<?$file = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"]["ID"], array('width' => 100, 'height' => 50), BX_RESIZE_IMAGE_PROPORTIONAL, true);?>
                    <a href="#" class="fancybox_detailed" data-id="detailed_<?=$arElement["ID"]?>"><img src="<?=$file["src"]?>" alt="<?=$arElement["NAME"]?>"></a>

					<?}?>

                </td>
                <td class="art">
                    <div class="name-holder fancybox_detailed" data-id="detailed_<?=$arElement["ID"]?>">
                        <span><?=$arElement["DISPLAY_PROPERTIES"]["ARTICUL"]["VALUE"]?></span>
                    </div>
                </td>
                <?
                $mera = $arElement["DISPLAY_PROPERTIES"]["UNITS"]["VALUE"];
                $ves = (float)$arElement["PROPERTIES"]["VES"]["VALUE"];
                if ($mera == "кг") {
                    $k = round(1000 / $ves, 5);

                    $k_val = "шт";
                    $e = 'руб/шт';
                } elseif ($mera == "шт") {
                    $k = round($ves / 1000, 5);

                    $k_val = "кг";
                    $e = 'руб/кг';
                } elseif ($mera == "тыс. шт") {
                    $k = round($ves, 2);

                    $k_val = "кг";
                    $e = 'руб/кг';
                }
                $ok = number_format(round($arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"]/$k, 2), 2, ', ', ' ');
                $rk = number_format(round($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]/$k, 2), 2, ', ', ' ');
                ?>
                <td class="nopadding-i">
                    <?if($arElement["DETAIL_TEXT"]){?><a href="#" class="fancybox_detailed" data-id="detailed_text_<?=$arElement["ID"]?>">
                            <img src="/images/i.png" alt="Информация"/></a><?}?>
                </td>
                <td class="name">
                    <div class="name_wrapper">
                        <div class="name-holder fancybox_detailed" data-id="detailed_<?=$arElement["ID"]?>">
                            <span><?=$arElement["NAME"]?></span>
                        </div>
                        <div class="description_holder" id="detailed_<?=$arElement["ID"]?>">
                            <h3><?=$arElement["PROPERTIES"]["NAIMENOVANIE"]["VALUE"]?></h3>
                            <table class="full">
                                <tr>
									<?if($arElement["PREVIEW_PICTURE"]){?>
                                    <td class="picture">
										<?
										$file = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"]["ID"], array('width' => 250, 'height' => 250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
										$file_big = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"]["ID"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
										?>
                                        <a href="<?=$file_big["src"]?>" class="fancybox"><img src="<?=$file["src"]?>" alt="<?=$arElement["NAME"]?>"></a>
                                    </td>
									<?}?>
                                    <td class="properties">
                                        <div class="buy">
                                            <div class="buy_helper_holder">
                                                <div class="buy_helper">
                                                    <?if($ves){?><div class="vesHelperHolder"><div class="vesHelper" data-val='<?=$k_val;?>' data-k="<?=$k;?>">0 шт</div></div><?}?>
                                                    <div class="input_holder"><input type="text" name="ITEM[<?=$arElement["ID"]?>]" data-price="<?=$arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]?>"></div>
	                                                <?if(count($countTips)){?>
                                                    <div class="help_values">
		                                                <?foreach($countTips as $c){?>
                                                        <a href="#" data-val="<?=$c?>">+<span><?=$c?></span></a>
		                                                <?}?>
                                                        <div class="clear"></div>
                                                    </div>
	                                                <?}?>
                                                </div><a href="#" class="add_to_cart_one one"><img src="/img/cart_buttton.png" alt=""></a>
                                            </div>
                                        </div>

                                        <table class="full">
                                            <tr>
                                                <td class="prop_name">Артикул:</td>
                                                <td class="prop_value"><?=$arElement["PROPERTIES"]["ARTICUL"]["VALUE"]?></td>
                                            </tr>
                                            <tr>
                                                <td class="prop_name">Единицы:</td>
                                                <td class="prop_value"><?=$arElement["PROPERTIES"]["UNITS"]["VALUE"]?></td>
                                            </tr>
                                            <tr>
                                                <td class="prop_name">Наличие:</td>
                                                <td class="prop_value"><?=(float)$arElement["PROPERTIES"]["OSTATOK"]["VALUE"]?></td>
                                            </tr>
                                            <tr>
                                                <td class="prop_name">Розничная цена:</td>
                                                <td class="prop_value"><?=$arElement["PROPERTIES"]["PRICE"]["VALUE"]?></td>
                                            </tr>
                                            <tr>
                                                <td class="prop_name">Оптовая цена:</td>
                                                <td class="prop_value"><?=$arElement["PROPERTIES"]["PRICE_OPT"]["VALUE"]?></td>
                                            </tr>
                                            <tr>
                                                <td class="prop_name">В упаковке:</td>
                                                <td class="prop_value"><?=$up1;?><?=($up2?'/'.$up2:'')?></td>
                                            </tr>
                                            <?if($arElement["PROPERTIES"]["VES"]["VALUE"]){?>
                                            <tr>
                                                <td class="prop_name">Вес 1000 шт.:</td>
                                                <td class="prop_value"><?=$arElement["PROPERTIES"]["VES"]["VALUE"];?> кг.</td>
                                            </tr>
                                            <?}?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="text">
											<?if($arElement["PREVIEW_TEXT"]!=''){?>
											<?=($arElement["PREVIEW_TEXT"])?>
											<?}elseif($arResult["DESCRIPTION"]!=''){?>
                                            <!--noindex--> <?=($arResult["DESCRIPTION"])?><!--/noindex-->
											<?}?>
                                        </div>
	                                    <?if($arElement['DETAIL_TEXT']){?>
		                                    <div class="detail_link"><a href="#" class="fancybox_detailed" data-id="detailed_text_<?=$arElement["ID"]?>">подробнее</a></div>
	                                        <div class="description_holder" id="detailed_text_<?=$arElement["ID"]?>">
		                                        <?=$arElement['DETAIL_TEXT'];?>
		                                    </div>
	                                    <?}?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td class="opt">
                    <div class="buy_helper_holder price">
                        <? if ($ves) { ?>
                            <div class="vesHelperHolder">
                                <div class="vesHelper" data-price="<?=coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"])?>">~ <?=$ok?> <?=$e?></div>
                            </div>
                        <? } ?>
                        <?= coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"]) ?></div></td>
                <td class="roz">
                    <div class="buy_helper_holder price">
                        <? if ($ves) { ?>
                            <div class="vesHelperHolder">
                                <div class="vesHelper" data-price="<?=coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"])?>">~ <?=$rk?> <?=$e?></div>
                            </div>
                        <? } ?>
                    <?= coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]) ?></td>
                <td class="upak"><?=$up1;?><?=($up2?'/'.$up2:'')?></td>
                <td class="avail"><?if((float)$arElement["PROPERTIES"]["OSTATOK"]["VALUE"] > 0) {
                        echo 'В наличии';
                    } else {
                        if ($arElement["PROPERTIES"]["NomenklaturaGeog"]["VALUE"] == 3) {
                            echo 'Временно отсутствует';
                        } else {
                            echo 'Под заказ';
                        }
                    }?></td>

                <td class="buy">
                    <div class="buy_helper_holder">
                        <div class="buy_helper">
                            <?if($ves){?><div class="vesHelperHolder"><div class="vesHelper" data-val='<?=$k_val;?>' data-k="<?=$k;?>">0 шт</div></div><?}?>
                            <div class="input_holder"><input type="text" name="ITEM[<?=$arElement["ID"]?>]" data-price="<?=$arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]?>"></div>
	                        <?if(count($countTips)){?>
                            <div class="help_values">
		                        <?foreach($countTips as $c){?>
                                <a href="#" data-val="<?=$c?>">+<span><?=$c?> <?=$arElement["DISPLAY_PROPERTIES"]["UNITS"]["VALUE"]?></span></a>
		                        <?}?>
                                <div class="clear"></div>
                            </div>
	                        <?}?>
                        </div>
                    </div>
                </td>
                <td class="mera"><?=$arElement["DISPLAY_PROPERTIES"]["UNITS"]["VALUE"]?></td>
                <td class="cart_td"><a href="#" class="add_to_cart_one"><img src="/img/cart_buttton.png" alt=""></a></td>
            </tr>
				<?endforeach; // foreach($arResult["ITEMS"] as $arElement):?>
            </tbody>
        </table>
        <div class="order">
            <div class="order_button">
                <a href="/cart/" onclick="$(this).closest('form').submit(); return false; " class="link_like_button">В корзину</a>
            </div>
            <div class="order_precount"></div>
            <div class="clear"></div>
        </div>
    </form>
</div>
<?
	if(sizeof($arResult["UF_SEE_ALSO"])>0 && is_array($arResult["UF_SEE_ALSO"])){
		global $arrFilter;
		$arrFilter=Array("!UF_PRODUCT"=>false, "SECTION_ID"=>$arResult["UF_SEE_ALSO"], "!PICTURE"=>false);
		?>
	<?$APPLICATION->IncludeComponent(
			"profi:catalog.section.list",
			"see_also",
			Array(
				"IBLOCK_TYPE" => "content",
				"IBLOCK_ID" => "1",
				"SECTION_ID" => "",
				"SECTION_CODE" => "",
				"SECTION_URL" => "",
				"COUNT_ELEMENTS" => "N",
				"TOP_DEPTH" => "5",
				"SECTION_FIELDS" => array("UF_PRICE_FROM", "PICTURE"),
				"SECTION_USER_FIELDS" => array("UF_PRODUCT", "UF_PRICE_FROM"),
				"ADD_SECTIONS_CHAIN" => "N",
				"CACHE_TYPE" => "N",
				"CACHE_TIME" => "0",
				"CACHE_GROUPS" => "N",
				"FILTER_NAME"=>"arrFilter",
				"NAV_COUNT"=>4,
				"SORT_BY"=>"rand",
				"SORT_ORDER"=>"desc"
			),
			false
		);?><?}?>
<div class="clear"></div>
<?}?>