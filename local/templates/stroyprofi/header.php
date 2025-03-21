<?php
use \Bitrix\Main\Page\Asset;

// Определяем, является ли пользователь покупателем киоска
global $USER;
$arGroups = [];

$rsGroups = \CUser::GetUserGroupEx($USER->GetID());
while($arGroup = $rsGroups->GetNext()) {
    $arGroups[] = $arGroup['STRING_ID'];
}

$isKioskBuyer = false;
if (in_array('KIOSK_BUYER', $arGroups)) {
    $isKioskBuyer = true;
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="mailru-domain" content="6EZGx3j1030Ofx7m" />
    <meta name="viewport" content="width=device-width,height=device-height,minimum-scale=1">
    <title><?php $APPLICATION->ShowTitle(); ?></title>
	<?php
    $APPLICATION->ShowHead();
	?>
    <link rel="stylesheet" href="/plugins/fancybox/jquery.fancybox.css">
    <script type="text/javascript" src="//yandex.st/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript" src="/plugins/fancybox/jquery.fancybox.pack.js"></script>
    <script type="text/javascript" src="/js/functions.js?123"></script>
    <script type="text/javascript" src="/js/jquery.tools.min.js"></script>
    <script src="/plugins/fancybox/fancybox.umd.js"></script>
    <script src="/local/templates/stroyprofi/js/decimal.min.js"></script>
    <!--<link rel="icon" href="/favicon.ico?1" type="image/ico">-->
    <link rel="icon" href="/favicon.svg" sizes="any" type="image/svg+xml">

    <script type="text/javascript" src='<?= SITE_TEMPLATE_PATH?>/plugins/slick/slick.js'></script>
    <script type="text/javascript" src="<?= SITE_TEMPLATE_PATH?>/script.js"></script>
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/plugins/slick/slick.css">
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/plugins/slick/slick-theme.css">
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/css_templates/products.css">
    <link rel="stylesheet" href="/plugins/fancybox/fancybox.css"/>
    <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH?>/css_templates/print_styles.css" media="print">

    <script src="/local/templates/stroyprofi/js/jquery.shCircleLoader.js"></script>
    <script src="/local/templates/stroyprofi/js/jquery.shCircleLoader-min.js"></script>
    <link rel="stylesheet" href="/local/templates/stroyprofi/js/jquery.shCircleLoader.css">

    <link href="<?= SITE_TEMPLATE_PATH?>/plugins/snowFlakes/snow.min.css" rel="stylesheet">
</head>
<body>
    <script src="<?= SITE_TEMPLATE_PATH?>/plugins/snowFlakes/Snow.js"></script>
    <script>
        new Snow ({
            iconColor: '#f7941d',
            showSnowBalls: true,
            showSnowBallsIsMobile: true,
            showSnowflakes: true,
            countSnowflake: 100,
            snowBallsLength: 10,
            snowBallIterations: 40,
            snowBallupNum: 1,
            snowBallIterationsInterval: 1000,
            clearSnowBalls: 20000,
        });
    </script>

<div id="shclDefault"></div>
<style>
    #shclDefault {
        position: absolute !important;
        left: 50%;
        top: 50%;
        z-index: 10000;
    }
</style>
<div class="notification" id="notification">Товар добавлен<br/>в <a href="/personal/cart/">корзину</a></div>
<?$APPLICATION->ShowPanel();?>
<div class="wrapper">
    <div class="width_wrapper">
        <div class="header">
            <div class="top">
                <div class="logo"><a href="/"><img src="/img/logo.png" alt=""></a></div>
                <div class="contacts">
                    <div class="phone"><?$APPLICATION->IncludeComponent(
						"bitrix:main.include",
						"",
						Array(
							"AREA_FILE_SHOW" => "file",
							"PATH" => "/includes/contact_phone.php",
							"EDIT_TEMPLATE" => ""
						),
						false
					);?></div>
					<?php
                    global $USER;
                    $arGroups = [];

                    $rsGroups = \CUser::GetUserGroupEx($USER->GetID());
                    while($arGroup = $rsGroups->GetNext()) {
                        $arGroups[] = $arGroup['STRING_ID'];
                    }

                    $isKioskBuyer = false;
                    if (in_array('KIOSK_BUYER', $arGroups) || strpos($_SERVER['HTTP_USER_AGENT'], 'KioskBrowser') !== false) {
                        $isKioskBuyer = true;
                    }

                    $APPLICATION->IncludeComponent(
					"bitrix:main.include",
					"",
					Array(
						"AREA_FILE_SHOW" => "file",
						"PATH" => "/includes/contact_email.php",
						"EDIT_TEMPLATE" => "",
                        "IS_KIOSK_PAYER" => $isKioskBuyer
					),
					false
				);?></div>
                <div class="address"><?$APPLICATION->IncludeComponent(
					"bitrix:main.include",
					"",
					Array(
						"AREA_FILE_SHOW" => "file",
						"PATH" => "/includes/contact_address.php",
						"EDIT_TEMPLATE" => ""
					),
					false
				);?></div>
                <?php
                global $USER;
                if(!$USER->IsAuthorized()) // Для неавторизованного
                {
                    global $APPLICATION;
                    $arFavorites = unserialize($_COOKIE["favorites"]);
                } else {
                    $idUser = $USER->GetID();
                    $rsUser = CUser::GetByID($idUser);
                    $arUser = $rsUser->Fetch();
                    $arFavorites = $arUser['UF_FAVORITES'];
                }
                ?>

                <div class="b-header-compare">
                    <a href="/catalog/compare/">
                        <svg id="compare-icon" class="compare-svg-icon-header" width="100%" height="100%" viewBox="0 0 18 16">
                            <path fill-rule="evenodd"
                                  clip-rule="evenodd"
                                  d="M13.4993 12.0003V11.125C13.4993 10.5036 14.0031 9.99985 14.6245 9.99985C15.2459 9.99985 15.7497 10.5036 15.7497 11.125V12.0003H17.0004C17.5525 12.0003 18 12.4478 18 12.9999C18 13.552 17.5525 13.9995 17.0004 13.9995H15.7497V14.8748C15.7497 15.4962 15.2459 16 14.6245 16C14.0031 16 13.4993 15.4962 13.4993 14.8748V13.9995H12.25C11.6979 13.9995 11.2503 13.552 11.2503 12.9999C11.2503 12.4478 11.6979 12.0003 12.25 12.0003H13.4993ZM0 12.9999C0 12.4478 0.447548 12.0003 0.999626 12.0003H8.00037C8.55245 12.0003 9 12.4478 9 12.9999C9 13.552 8.55245 13.9995 8.00037 13.9995H0.999626C0.447548 13.9995 0 13.552 0 12.9999ZM0 0.999625C0 0.447547 0.447548 0 0.999626 0H17.0004C17.5525 0 18 0.447547 18 0.999625C18 1.5517 17.5525 1.99925 17.0004 1.99925H0.999626C0.447548 1.99925 0 1.5517 0 0.999625ZM0 6.99977C0 6.4477 0.447548 6.00015 0.999626 6.00015H17.0004C17.5525 6.00015 18 6.4477 18 6.99977C18 7.55185 17.5525 7.9994 17.0004 7.9994H0.999626C0.447548 7.9994 0 7.55185 0 6.99977Z"
                                  fill="#000"
                            />
                        </svg>
                    </a>

                    <span class="compare-title"><a href="/catalog/compare/">Сравнение</a></span>
                    <?php
                    /** Лист сравнения */
                    $compareElements = [];
                    foreach ($_SESSION['CATALOG_COMPARE_LIST'] as $compareListsElements) {
                        foreach ($compareListsElements['ITEMS'] as $element) {
                            $compareElements[] = $element['ID'];
                        }
                    }
                    ?>
                    <span class="compare-count"><?= count($compareElements)?></span>
                </div>

                <div class="b-header-favorite">
                    <a href="/personal/wishlist/">
                        <svg  class="favorite-svg-icon-header" title="Избранное" width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="#8899a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </a>

                    <span class="favorite-title"><a href="/personal/wishlist/">Избранное</a></span>
                    <span class="favorite-count"><?= (!$arFavorites) ? '0' : count($arFavorites)?></span>
                </div>

                <div class="cart">
                    <div class="inner">
                        <a href="/personal/cart" class="img_cart_link"></a>
                        <?php
                        $APPLICATION->IncludeComponent(
                            "bitrix:sale.basket.basket.line",
                            "",
                            array(
                                "PATH_TO_BASKET" => SITE_DIR."personal/cart/",
                                "PATH_TO_PERSONAL" => SITE_DIR."personal/",
                                "SHOW_PERSONAL_LINK" => "N",
                                "SHOW_NUM_PRODUCTS" => "N",
                                "SHOW_TOTAL_PRICE" => "Y",
                                "SHOW_PRODUCTS" => "N",
                                "POSITION_FIXED" =>"N",
                                "SHOW_AUTHOR" => "Y",
                                "PATH_TO_REGISTER" => SITE_DIR."account/register/",
                                "PATH_TO_PROFILE" => SITE_DIR."personal/",
                                "PATH_TO_AUTHORIZE" => SITE_DIR."account/auth/",
                            ),
                            false,
                            array()
                        );?>
                    </div>
                </div>
                <div class="clear"></div>
            </div>


       <!-- Бегущая строка -->

<!--            <div class="marquee">-->
<!--                <span>-->
<!--                    --><?php
//                    $APPLICATION->IncludeComponent("bitrix:main.include","",Array(
//                            "AREA_FILE_SHOW" => "file",
//                            "PATH" => "/local/include_file/top_marquee.php",
//                        )
//                    );
//                    ?>
<!--                </span>-->
<!--            </div>-->

            <div class="dark_line">
                <div class="corners left"><div class="right"></div></div>
                <div class="content">
                    <div class="shurup shurup_left"></div>
                    <div class="shurup shurup_right"></div>
                    <?php
                        $APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "simple",
                        Array(
                            "ROOT_MENU_TYPE" => "top",
                            "MAX_LEVEL" => "1",
                            "CHILD_MENU_TYPE" => "left",
                            "USE_EXT" => "N",
                            "DELAY" => "N",
                            "ALLOW_MULTI_SELECT" => "N",
                            "MENU_CACHE_TYPE" => "A",
                            "MENU_CACHE_TIME" => "3600",
                            "MENU_CACHE_USE_GROUPS" => "N",
                            "MENU_CACHE_GET_VARS" => array()
                        ),
                        false
                    );?>
                    <div class="search">
                        <form action="/search/" method="get">
                            <input type="text" class="search-input onblur <?=($_GET['q']?"":"blured");?>" rel="Поиск по наименованиям и артикулам" name="q" value="<?=($_GET['q']?htmlspecialchars($_GET['q']):"Поиск по наименованиям и артикулам");?>">
                            <input type="submit" value="" class="search-submit">
							<?
							$where = ($_GET['where'] == "articul")?"articul":"name";
							?>
                            <a href="#" class="search_close">&times;</a>
                            <div class="search_results"></div>
                        </form>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div class="page_content">
			<?
			$mclose=(isset($_COOKIE["menustat"]) && $_COOKIE["menustat"]=="closed");
			$showLeftPanel = false;
			$showLeftPanel = ((CSite::InDir("/articles")) || ((CSite::InDir("/catalog")) && ($APPLICATION->GetCurPage() != '/catalog/')));
			$inCatalog = CSite::InDir("/catalog/");
			$cPage = ($APPLICATION->GetCurPage() == '/catalog/') ;
			?>
			<?if($inCatalog && $showLeftPanel){?><a class="open_left" <?=($mclose?'style="display:block;"':'')?> href="#"></a><?}

            $APPLICATION->GetCurPage() == '/account/auth/';
            ?>
            <table class="full content_table">
				<tr <?=$APPLICATION->GetCurPage() == '/account/auth/' ? 'style="display: contents"' : 'style="display: contents"'?>>
                <?
				if($showLeftPanel){?>
                    <td class="left_block_holder" <?=(($inCatalog&&$mclose)?'style="display:none;"':'')?>>
                        <div class="left_block">
                            <div class="main_navigation">
                                <?if($inCatalog && $showLeftPanel){?><a class="close_block" href="#"></a><?}?>
								<?$APPLICATION->IncludeComponent(
								"bitrix:menu",
								"left",
								Array(
									"ROOT_MENU_TYPE" => "left",
									"MAX_LEVEL" => "3",
									"CHILD_MENU_TYPE" => "left",
									"USE_EXT" => "Y",
									"DELAY" => "N",
									"ALLOW_MULTI_SELECT" => "Y",
									"MENU_CACHE_TYPE" => "N",
									"MENU_CACHE_TIME" => "3600",
									"MENU_CACHE_USE_GROUPS" => "Y",
									"MENU_CACHE_GET_VARS" => array()
								),
								false
							);?>
                            </div><br/>
                            <script type="text/javascript" src="/js/orphus.js"></script>

                            <div class="b-right_banner">
                                <?php
                                $APPLICATION->IncludeComponent(
                                    "bitrix:news.list",
                                    "banners_right",
                                    array(
                                        "DISPLAY_DATE" => "N",
                                        "DISPLAY_NAME" => "N",
                                        "DISPLAY_PICTURE" => "N",
                                        "DISPLAY_PREVIEW_TEXT" => "N",
                                        "AJAX_MODE" => "N",
                                        "IBLOCK_TYPE" => "content",
                                        "IBLOCK_ID" => "8",
                                        "NEWS_COUNT" => "999",
                                        "SORT_BY1" => "ACTIVE_FROM",
                                        "SORT_ORDER1" => "DESC",
                                        "SORT_BY2" => "SORT",
                                        "SORT_ORDER2" => "ASC",
                                        "FILTER_NAME" => "",
                                        "FIELD_CODE" => ["PREVIEW_PICTURE"],
                                        "PROPERTY_CODE" => ["LINK"],
                                        "CHECK_DATES" => "Y",
                                        "DETAIL_URL" => "",
                                        "PREVIEW_TRUNCATE_LEN" => "",
                                        "ACTIVE_DATE_FORMAT" => "d.m.Y",
                                        "SET_TITLE" => "N",
                                        "SET_STATUS_404" => "N",
                                        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                                        "ADD_SECTIONS_CHAIN" => "N",
                                        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                                        "PARENT_SECTION" => "",
                                        "PARENT_SECTION_CODE" => "",
                                        "CACHE_TYPE" => "A",
                                        "CACHE_TIME" => "36000000",
                                        "CACHE_FILTER" => "N",
                                        "CACHE_GROUPS" => "Y",
                                        "DISPLAY_TOP_PAGER" => "N",
                                        "DISPLAY_BOTTOM_PAGER" => "N",
                                        "PAGER_TITLE" => "Новости",
                                        "PAGER_SHOW_ALWAYS" => "N",
                                        "PAGER_TEMPLATE" => "",
                                        "PAGER_DESC_NUMBERING" => "N",
                                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                        "PAGER_SHOW_ALL" => "N",
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "N",
                                        "AJAX_OPTION_HISTORY" => "N"
                                    ),
                                    $component
                                );

                                ?>
                            </div>

                            <div style="text-align: center;"><a href="http://orphus.ru" id="orphus" target="_blank"><img class="orphus" alt="Система Orphus" src="/images/orphus.gif" /></a></div>

	                        <?$APPLICATION->IncludeComponent(
							"bitrix:news.list",
							"banners",
							Array(
								"DISPLAY_DATE" => "N",
								"DISPLAY_NAME" => "N",
								"DISPLAY_PICTURE" => "N",
								"DISPLAY_PREVIEW_TEXT" => "N",
								"AJAX_MODE" => "N",
								"IBLOCK_TYPE" => "content",
								"IBLOCK_ID" => "3",
								"NEWS_COUNT" => "3",
								"SORT_BY1" => "ACTIVE_FROM",
								"SORT_ORDER1" => "DESC",
								"SORT_BY2" => "SORT",
								"SORT_ORDER2" => "ASC",
								"FILTER_NAME" => "",
								"FIELD_CODE" => array("PREVIEW_PICTURE"),
								"PROPERTY_CODE" => array("LINK"),
								"CHECK_DATES" => "Y",
								"DETAIL_URL" => "",
								"PREVIEW_TRUNCATE_LEN" => "",
								"ACTIVE_DATE_FORMAT" => "d.m.Y",
								"SET_TITLE" => "N",
								"SET_STATUS_404" => "N",
								"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
								"ADD_SECTIONS_CHAIN" => "N",
								"HIDE_LINK_WHEN_NO_DETAIL" => "N",
								"PARENT_SECTION" => "",
								"PARENT_SECTION_CODE" => "",
								"CACHE_TYPE" => "A",
								"CACHE_TIME" => "36000000",
								"CACHE_FILTER" => "N",
								"CACHE_GROUPS" => "Y",
								"DISPLAY_TOP_PAGER" => "N",
								"DISPLAY_BOTTOM_PAGER" => "N",
								"PAGER_TITLE" => "Новости",
								"PAGER_SHOW_ALWAYS" => "N",
								"PAGER_TEMPLATE" => "",
								"PAGER_DESC_NUMBERING" => "N",
								"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
								"PAGER_SHOW_ALL" => "N",
								"AJAX_OPTION_JUMP" => "N",
								"AJAX_OPTION_STYLE" => "N",
								"AJAX_OPTION_HISTORY" => "N"
							),
							false
						);?>



                        </div>
                    </td><?}?>

                <td>
                    <div class="main_block<?=(!$showLeftPanel?' no-left':'')?>">
                        <div class="breadcrumbs">
							<?$APPLICATION->IncludeComponent(
							"bitrix:breadcrumb",
							"",
							Array(),
							false
						);?>
                        </div>
                        <h1><?$APPLICATION->ShowTitle();?></h1>
