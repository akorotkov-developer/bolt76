<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>-->

<?
/*\Bitrix\Main\UI\Extension::load("ui.bootstrap4");*/

if(sizeof($arResult["SECTIONS"])>0){
    ?>
<div class="sections_list main">
            <?foreach($arResult["SECTIONS"] as $section){
            $this->AddEditAction($section['ID'], $section['EDIT_LINK'], CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_EDIT"));
            $this->AddDeleteAction($section['ID'], $section['DELETE_LINK'], CIBlock::GetArrayByID($section["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM')));


            ?>
            <a class="section"  href="<?=$section["SECTION_PAGE_URL"]?>"  id="<?=$this->GetEditAreaId($section['ID']);?>">
                <span class="picture"><?
                if($section["PICTURE"]["ID"]>0){
                    //$file = CFile::ResizeImageGet($section["PICTURE"]["ID"], array('width' => 300, 'height' => 94), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                    $file = CFile::GetPath($section["PICTURE"]["ID"]);
                ?>
                <img src="<?=$file?>" alt="" style="
                  max-width:230px;
                  max-height:95px;
                  width: auto;
                  height: auto;
                "
                >
                <?}?></span>
                <span class="link<?=(mb_strlen($section["NAME"])>23?' small-height':'')?>"><?=$section["NAME"]?></span>
            </a>
        <?}?>
</div>
<?}?>