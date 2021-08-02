<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="section_elements">
<?foreach($arResult["SECTIONS"] as $arSection):
	$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_EDIT"));
	$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM')));

?>
    <div class="section_element" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
        <div class="picture">
            <?
            if($arSection["PICTURE"]["ID"]){
            $file = CFile::ResizeImageGet($arSection["PICTURE"]["ID"], array('width' => 70, 'height' => 70), BX_RESIZE_IMAGE_PROPORTIONAL, true);
            ?>
            <a href="<?=$arSection["SECTION_PAGE_URL"]?>"><img src="<?=$file["src"]?>" alt=""></a>
            <?}?>
        </div>
        <div class="element_content">
            <div class="name"><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?></a></div>
            <div class="description"><?=strip_tags($arSection["DESCRIPTION"])?></div>
        </div>
        <?if((float)$arSection["UF_PRICE_FROM"]>0){?>
        <div class="price">
            Цена от <br>
            <span><?=coolPrice($arSection["UF_PRICE_FROM"])?></span> р.
        </div>
        <?}?>
        <div class="clear"></div>
    </div>
<?endforeach?>
</div>
