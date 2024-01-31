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