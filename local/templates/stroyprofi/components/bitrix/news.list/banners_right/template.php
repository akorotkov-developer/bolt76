<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="b-right-banner-list">
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
        <div class="b-right-banner-list__item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
            <a class="b-right-banner-list__item-link" href="<?=$arItem["PROPERTIES"]["LINK"]["VALUE"]?>"><img class="b-right-banner-list__item-img" src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt="<?=$arItem["NAME"]?>"></a>
        </div>
    <?endforeach;?>
</div>

<script>
    $('.b-right-banner-list').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: false,
        autoplaySpeed: 4000,
        nextArrow: '<div class="prev_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowleft.png"></div>',
        prevArrow: '<div class="next_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowright.png"></div>',
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    nextArrow: '<div class="prev_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowleft.png"></div>',
                    prevArrow: '<div class="next_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowright.png.png"></div>',
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    nextArrow: '<div class="prev_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowleft.png"></div>',
                    prevArrow: '<div class="next_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowright.png"></div>',
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    nextArrow: '<div class="prev_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowleft.png"></div>',
                    prevArrow: '<div class="next_slick_arrow"><img src="<?= SITE_TEMPLATE_PATH?>/plugins/slick/images/arrowright.png"></div>',
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });
</script>