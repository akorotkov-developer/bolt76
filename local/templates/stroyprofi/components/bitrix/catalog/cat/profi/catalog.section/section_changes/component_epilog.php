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

                        $('.product-layout').each(function(i, obj) {
                            elementId = $(obj).attr('data-elementid');

                            if (products[elementId]) {
                                $(obj).find('.is-in-basket').html('<div class="in_basket">' + Number(products[elementId].QUANTITY) + ' в корзине <img class="in_basket_delete" data-element="' + elementId + '" src="/local/templates/stroyprofi/components/bitrix/catalog/cat/profi/catalog.section/section_descr/images/trash.png"></div>');
                            }
                        });
                        $('.element_product_tr').each(function(i, obj) {
                            elementId = $(obj).attr('data-elementid');

                            if (products[elementId]) {
                                $(obj).find('.is-in-basket').html('<div class="in_basket">' + Number(products[elementId].QUANTITY) + ' в корзине <img class="in_basket_delete" data-element="' + elementId + '" src="/local/templates/stroyprofi/components/bitrix/catalog/cat/profi/catalog.section/section_descr/images/trash.png"></div>');
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
                        $('.btn.btn-cart').each(function(i, obj) {
                            elementId = $(obj).attr('data-elementid');

                            if (products[elementId]) {
                                $(obj).html('В корзине (' + Number(products[elementId].QUANTITY) + ')');
                                if (!$(obj).hasClass('basket-added')) {
                                    $(obj).addClass('basket-added');
                                }
                            } else {
                                $(obj).html('В корзину');
                                $(obj).removeClass('basket-added');
                            }
                        });
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
                    window.basketController.setLinks();
                },
                error: function(jqXHR, textStatus, errorThrown){ // Ошибка
                    console.log('Error: '+ errorThrown);
                }
            });
        });
    });
</script>
