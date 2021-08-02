<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(sizeof($arResult["SECTIONS"])>0){?>



<div class="catalog_see_also">
    <div class="caption">Посмотрите также</div>
    <?foreach($arResult["SECTIONS"] as $section){?>
    <div class="catalog_item">
       <!-- <div class="sticker">NEW</div> -->
        <?
        $file = CFile::ResizeImageGet($section["PICTURE"]["ID"], array('width' => 100, 'height' => 80), BX_RESIZE_IMAGE_PROPORTIONAL, true);
        ?>
        <div class="picture"><a href="<?=$section["SECTION_PAGE_URL"]?>"><img src="<?=$file["src"]?>" alt="<?=$section["NAME"]?>"></a></div>
        <div class="name"><a href="<?=$section["SECTION_PAGE_URL"]?>"><?=$section["NAME"]?></a></div>
        <div class="price">Цена от <b><?=$section["UF_PRICE_FROM"]?> р.</b></div>
    </div>
    <?}?>


    <div class="clear"></div>
</div>
<?}?>