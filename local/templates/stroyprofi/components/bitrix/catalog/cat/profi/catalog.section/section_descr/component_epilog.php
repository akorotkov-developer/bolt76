<?php
if (CModule::IncludeModule("iblock")) {
    $dbRez = CIBlockSection::GetList(
        [],
        [
            'IBLOCK_ID' => 1, '=ID' => $arResult['ID']
        ],
        false,
        ['ID', 'UF_SECTION_META_KEYWORDS', 'UF_SECTION_META_DESCRIPTION']
    );

    while ($arRez = $dbRez->fetch()) {
        $item = $arRez;
    }

    $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues(1, $arResult['ID']);
    $arSEO = $ipropSectionValues->getValues();

    global $APPLICATION;
    $APPLICATION->SetPageProperty('keywords', $arSEO['SECTION_META_KEYWORDS']);
    $APPLICATION->SetPageProperty('description', $arSEO['SECTION_META_DESCRIPTION']);
}

/** Отметка товаров, которые в корзине */
// Получим все товары из корзины
?>

<?php
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
foreach($arFavorites as $k => $favoriteItem) {
    ?>
    <script>
        $( document ).ready(function() {
            if($('.favorite-svg-icon[data-product-id="' + <?=$favoriteItem ?> + '"]').length > 0) {
                $('.favorite-svg-icon[data-product-id="' + <?=$favoriteItem ?> + '"]').attr('class', 'favorite-svg-icon active');;
            }
        });
    </script>
<?php } ?>

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
                        var products = JSON.parse(data);
                        var elementId;

                        $('.element_product_tr').each(function(i, obj) {
                            elementId = $(obj).attr('data-elementid');

                            if (products[elementId]) {
                                if ($(obj).find('.buy').find('div.in_basket').length > 0) {
                                    $(obj).find('.buy').find('div.in_basket').remove();
                                }
                                $(obj).find('.buy').append('<div class="in_basket">' + Number(products[elementId].QUANTITY) + ' в корзине <img class="in_basket_delete" data-element="' + elementId + '" src="/local/templates/stroyprofi/components/bitrix/catalog/cat/profi/catalog.section/section_descr/images/trash.png"></div>');
                            }
                        });
                        $('.btn-list-add-to-cart').each(function(i, obj) {
                            elementId = $(obj).attr('data-elementid');

                            if (products[elementId]) {
                                $(obj).val('В корзине (' + Number(products[elementId].QUANTITY) + ')');
                                if (!$(obj).hasClass('basket-added')) {
                                    $(obj).addClass('basket-added');
                                }
                            } else {
                                $(obj).val('В корзину');
                                $(obj).removeClass('basket-added');
                            }
                        });
                    }
                });
            }
        };
        window.basketController.setLinks();

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
                    window.basketController.setLinks();
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

        document.querySelectorAll('.b-compare .compare-svg-icon-element-list').forEach(element => {
            // Получаем значение data-id
            const dataId = element.getAttribute('data-product-id');

            // Проверяем, есть ли dataId в массиве idArray
            const parentCompare = element.closest('.b-compare');
            if (idArray.includes(dataId)) {
                // Находим родительский элемент .b-compare и добавляем класс active
                if (parentCompare) {
                    parentCompare.classList.add('active');
                }
            } else {
                if (parentCompare) {
                    parentCompare.classList.remove('active');
                }
            }
        });
    });
</script>
