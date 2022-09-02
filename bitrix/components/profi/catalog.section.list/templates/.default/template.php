<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(sizeof($arResult["SECTIONS"])>0){?>
<div class="sections_list">
    <?foreach($arResult["SECTIONS"] as $arSection) {
	$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_EDIT"));
	$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM')));
	?>
    <?if($arSection["PICTURE"]["ID"]>0){$file = CFile::ResizeImageGet($arSection["PICTURE"]["ID"], array('width' => 128, 'height' => 130), BX_RESIZE_IMAGE_PROPORTIONAL, true);?><?}?>
    <a class="section"  href="<?=$arSection["SECTION_PAGE_URL"]?>" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
        <span class="picture"  style="background-image: url('<?if($arSection["PICTURE"]["ID"]>0){echo $file["src"]; }else{?>/img/no-image.jpeg<?}?>');"></span>
        <?php
        $sSectionName = preg_replace('/^\d+\s+/', '', $arSection["NAME"]);
        ?>
        <span class="link"><?=$sSectionName?></span>
    </a>
    <?}?>
    <div class="clear"></div>
</div>
<?}?>