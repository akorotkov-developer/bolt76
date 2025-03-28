<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

/**
 * @var array $templateData
 * @var array $arParams
 * @var string $templateFolder
 * @global CMain $APPLICATION
 */

global $APPLICATION;

// Установка заголовка
$APPLICATION->SetPageProperty('title', $arResult['NAME']);

if (isset($templateData['TEMPLATE_THEME']))
{
	$APPLICATION->SetAdditionalCSS($templateFolder.'/themes/'.$templateData['TEMPLATE_THEME'].'/style.css');
	$APPLICATION->SetAdditionalCSS('/bitrix/css/main/themes/'.$templateData['TEMPLATE_THEME'].'/style.css', true);
}

if (!empty($templateData['TEMPLATE_LIBRARY']))
{
	$loadCurrency = false;

	if (!empty($templateData['CURRENCIES']))
	{
		$loadCurrency = Loader::includeModule('currency');
	}

	CJSCore::Init($templateData['TEMPLATE_LIBRARY']);
	if ($loadCurrency)
	{
		?>
		<script>
			BX.Currency.setCurrencies(<?=$templateData['CURRENCIES']?>);
		</script>
		<?
	}
}

if (isset($templateData['JS_OBJ']))
{
	?>
	<script>
		BX.ready(BX.defer(function(){
			if (!!window.<?=$templateData['JS_OBJ']?>)
			{
				window.<?=$templateData['JS_OBJ']?>.allowViewedCount(true);
			}
		}));
	</script>

	<?
	// check compared state
	if ($arParams['DISPLAY_COMPARE'])
	{
		$compared = false;
		$comparedIds = array();
		$item = $templateData['ITEM'];

		if (!empty($_SESSION[$arParams['COMPARE_NAME']][$item['IBLOCK_ID']]))
		{
			if (!empty($item['JS_OFFERS']))
			{
				foreach ($item['JS_OFFERS'] as $key => $offer)
				{
					if (array_key_exists($offer['ID'], $_SESSION[$arParams['COMPARE_NAME']][$item['IBLOCK_ID']]['ITEMS']))
					{
						if ($key == $item['OFFERS_SELECTED'])
						{
							$compared = true;
						}

						$comparedIds[] = $offer['ID'];
					}
				}
			}
			elseif (array_key_exists($item['ID'], $_SESSION[$arParams['COMPARE_NAME']][$item['IBLOCK_ID']]['ITEMS']))
			{
				$compared = true;
			}
		}

		if ($templateData['JS_OBJ'])
		{
			?>
			<script>
				BX.ready(BX.defer(function(){
					if (!!window.<?=$templateData['JS_OBJ']?>)
					{
						window.<?=$templateData['JS_OBJ']?>.setCompared('<?=$compared?>');

						<? if (!empty($comparedIds)): ?>
						window.<?=$templateData['JS_OBJ']?>.setCompareInfo(<?=CUtil::PhpToJSObject($comparedIds, false, true)?>);
						<? endif ?>
					}
				}));
			</script>
			<?
		}
	}

	// select target offer
	$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	$offerNum = false;
	$offerId = (int)$this->request->get('OFFER_ID');
	$offerCode = $this->request->get('OFFER_CODE');

	if ($offerId > 0 && !empty($templateData['OFFER_IDS']) && is_array($templateData['OFFER_IDS']))
	{
		$offerNum = array_search($offerId, $templateData['OFFER_IDS']);
	}
	elseif (!empty($offerCode) && !empty($templateData['OFFER_CODES']) && is_array($templateData['OFFER_CODES']))
	{
		$offerNum = array_search($offerCode, $templateData['OFFER_CODES']);
	}

	if (!empty($offerNum))
	{
		?>
		<script>
			BX.ready(function(){
				if (!!window.<?=$templateData['JS_OBJ']?>)
				{
					window.<?=$templateData['JS_OBJ']?>.setOffer(<?=$offerNum?>);
				}
			});
		</script>
		<?
	}
}

/** Дополнительно проставить избранное с помощью скрипта (нужно из-за того, что страницы кэшируются)*/
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

/* Меняем отображение сердечка товара */
foreach($arFavorites as $k => $favoriteItem):
    ?>
    <script>
        $( document ).ready(function() {
            if($('.favorite-svg-icon[data-product-id="' + <?=$favoriteItem ?> + '"]').length > 0) {
                $('.favorite-svg-icon[data-product-id="' + <?=$favoriteItem ?> + '"]').attr('class', 'favorite-svg-icon active');;
            }
        });
    </script>
<?php endforeach; ?>

<?php
/** Лист сравнения */
$compareElements = [];
foreach ($_SESSION['CATALOG_COMPARE_LIST'] as $compareListsElements) {
    foreach ($compareListsElements['ITEMS'] as $element) {
        $compareElements[] = $element['ID'];
    }
}
$compareElements = json_encode($compareElements);
?>

<script>
    $( document ).ready(function() {
        window.basketController = {
            setLinks: function () {
                $.ajax({
                    url: '/local/ajax/setbasketlinks.php',
                    method: 'get',
                    data: {},
                    success: function (data) {
                        var productsInBasket = JSON.parse(data);
                        var currentProductId = '<?= $arResult['ID']?>';

                        for (var productId in productsInBasket) {
                            if (productId == currentProductId) {
                                $('.is-in-basket').html('<div class="in_basket">' + Number(productsInBasket[productId].QUANTITY) + ' в корзине <img class="in_basket_delete" data-element="' + productId + '" src="<?= SITE_TEMPLATE_PATH?>/components/bitrix/catalog/cat/profi/catalog.section/section_descr/images/trash.png"></div>');
                            }
                        }
                    }
                });
            }
        };
        window.basketController.setLinks();

        // Удаление товара из корзины
        $('body').on('click', '.in_basket_delete', function(e) {
            var param = 'idBasketElement=' + $(this).attr('data-element');
            var inBasketBlock = $(this).parent();
            $.ajax({
                url:     '/local/ajax/deletebasketelement.php', // URL отправки запроса
                type:     'GET',
                dataType: 'html',
                data: param,
                success: function(response) {
                    if ($.trim(response) != '') {
                        $('.cart_info_holder').html(response);
                    }
                    inBasketBlock.remove();
                },
                error: function(jqXHR, textStatus, errorThrown){ // Ошибка
                    console.log('Error: '+ errorThrown);
                }
            });
        });

        // Исходная JSON-строка
        let json = '<?= $compareElements ?>';
        // Преобразуем JSON-строку в массив
        let idArray = JSON.parse(json);
        // Текущий ID для проверки
        let currentId = '<?= $arResult['ID'] ?>'; // или let currentId = 172319; (если ID — число)

        // Проверяем, существует ли currentId в массиве
        // Ищем элемент с классом compare-svg-icon-element-detail и атрибутом data-product-id=currentId
        let targetElement = document.querySelector(`.compare-svg-icon-element-detail[data-product-id="${currentId}"]`);
        if (idArray.includes(currentId.toString())) { // Преобразуем currentId в строку, если массив содержит строки
            // Если элемент найден
            if (targetElement) {
                // Добавляем класс active его родителю
                targetElement.closest('.b-compare').classList.add('active');
            }
        } else {
            if (targetElement) {
                targetElement.closest('.b-compare').classList.remove('active');
            }
        }
    });
</script>
