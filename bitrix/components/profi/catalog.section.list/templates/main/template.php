<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(sizeof($arResult["SECTIONS"])>0){?>
<div class="sections_list main">
    <?foreach($arResult["SECTIONS"] as $section){
    $this->AddEditAction($section['ID'], $section['EDIT_LINK'], CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_EDIT"));
    $this->AddDeleteAction($section['ID'], $section['DELETE_LINK'], CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM')));


    ?>
    <a class="section" href="<?=$section["SECTION_PAGE_URL"]?>"  id="<?=$this->GetEditAreaId($section['ID']);?>">
        <span class="picture"><?
        if($section["PICTURE"]["ID"]>0){
            $file = CFile::ResizeImageGet($section["PICTURE"]["ID"], array('width' => 300, 'height' => 94), BX_RESIZE_IMAGE_PROPORTIONAL, true);
        ?>
        <img src="<?=$file["src"]?>" alt="">
        <?}?></span>

        <?php
        $sSectionName = preg_replace('/^\d+\s+/', '', $section["NAME"]);
        ?>
        <span class="link<?=(mb_strlen($section["NAME"])>23?' small-height':'')?>"><?=$sSectionName?></span>
    </a>
    <?}?>
    <div class="clear"></div>
</div>
<?}?>