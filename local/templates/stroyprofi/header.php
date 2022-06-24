<?php
use \Bitrix\Main\Page\Asset;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title><?$APPLICATION->ShowTitle();?></title>
	<?$APPLICATION->ShowHead();?>
    <link rel="stylesheet" href="/fancybox/jquery.fancybox.css">
    <script type="text/javascript" src="//yandex.st/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript" src="/fancybox/jquery.fancybox.pack.js"></script>
    <script type="text/javascript" src="/js/functions.js?123"></script>
    <script type="text/javascript" src="/js/jquery.tools.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
    <link rel="icon" href="/favicon.ico?1" type="image/ico">

    <script type="text/javascript" src='<?= SITE_TEMPLATE_PATH?>/plugins/slick/slick.js'></script>
    <script type="text/javascript" src="<?= SITE_TEMPLATE_PATH?>/script.js"></script>
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/plugins/slick/slick.css">
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/plugins/slick/slick-theme.css">
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/css_templates/products.css">
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css"
    />
</head>
<body>
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
					<?$APPLICATION->IncludeComponent(
					"bitrix:main.include",
					"",
					Array(
						"AREA_FILE_SHOW" => "file",
						"PATH" => "/includes/contact_email.php",
						"EDIT_TEMPLATE" => ""
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
                <div class="b-header-favorite">
                    <a href="/personal/wishlist/">
                        <svg  class="favorite-svg-icon-header" title="Избранное" width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="#8899a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </a>

                    <span class="favorite-title"><a href="/personal/wishlist/">Избранное</a></span>
                    <span class="favorite-count"><?= count($arFavorites)?></span>
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
            <div class="dark_line">
                <div class="corners left"><div class="right"></div></div>
                <div class="content">
                    <div class="shurup shurup_left"></div>
                    <div class="shurup shurup_right"></div>
                    <div class="menu"><?$APPLICATION->IncludeComponent(
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
					);?></div>
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
			<?if($inCatalog && $showLeftPanel){?><a class="open_left" <?=($mclose?'style="display:block;"':'')?> href="#"></a><?}?>
            <table class="full content_table">
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
                            <div style="text-align: center;"><a href="http://orphus.ru" id="orphus" target="_blank"><img alt="Система Orphus" src="/images/orphus.gif" border="0" width="257" height="48" /></a></div>

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
						);?></div>
                    </td><?}?>
                <td style="width: 1px;"><div style="width:1px;height: 600px;"></div></td>
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
                        <h1><?$APPLICATION->ShowTitle("");?></h1>
