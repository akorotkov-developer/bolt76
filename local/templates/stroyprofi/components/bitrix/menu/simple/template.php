<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? if (!empty($arResult)): ?>
    <?
    global $USER;
    $arGroups = [];

    $rsGroups = \CUser::GetUserGroupEx($USER->GetID());
    while ($arGroup = $rsGroups->GetNext()) {
        $arGroups[] = $arGroup['STRING_ID'];
    }

    $isKioskBuyer = false;
    if (in_array('KIOSK_BUYER', $arGroups) || strpos($_SERVER['HTTP_USER_AGENT'], 'KioskBrowser') !== false) {
        $isKioskBuyer = true;
    }

    foreach ($arResult as $arItem):
        if ($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1)
            continue;

        $arExcludedByKiosk = ['/delivery/', '/payment/'];
        if ($isKioskBuyer && in_array($arItem["LINK"], $arExcludedByKiosk)) {
            continue;
        }
        ?>

        <?php
        if ($arItem["LINK"] == '/price') {?>
                <span class="top-parent-menu-item">
                    <?= $arItem["TEXT"]?>
                    <ul class="top-submenu">
                        <li class="top-submenu-item"><a href="/price/price.xlsx" download="">Excel</a></li>
                        <li class="top-submenu-item"><a href="/price/price.pdf" download="">Pdf</a></li>
                    </ul>
                </span>
            <?
        } else {
            ?>

            <? if ($arItem["SELECTED"]): ?>
                <a href="<?= $arItem["LINK"] ?>" class="selected"><?= $arItem["TEXT"] ?></a>
            <? else: ?>
                <a href="<?= $arItem["LINK"] ?>"><?= $arItem["TEXT"] ?></a>
            <? endif ?>

            <?php
        }
        ?>

    <? endforeach ?>
<? endif ?>