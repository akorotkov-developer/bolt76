<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<div class="menu">
    <? if (!empty($arResult)) { ?>
        <?php
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

        foreach ($arResult as $arItem) {
            if ($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1)
                continue;

            $arExcludedByKiosk = ['/delivery/', '/payment/', '/price'];
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
        }
    } ?>
</div>
<div class="menu_mobile">
    <nav class="navbar">
        <div class="navbar-container container">
            <input type="checkbox" name="" id="">
            <div class="hamburger-lines">
                <span class="line line1"></span>
                <span class="line line2"></span>
                <span class="line line3"></span>
            </div>
            <ul class="menu-items">
                <?php
                foreach ($arResult as $arItem) {
                    if ($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1)
                        continue;
                    ?>

                    <?php
                    if ($arItem["LINK"] == '/price') {?>

                        <li><a href="/price/price.xlsx" download="">Скачать Excel</a></li>
                        <li><a href="/price/price.pdf" download="">Скачать Pdf</a></li>

                        <?php
                    } else { ?>

                        <li><a href="<?= $arItem["LINK"] ?>" class="<?= ($arItem["SELECTED"]) ? 'selected' : ''?>"><?= $arItem["TEXT"] ?></a></li>

                        <?php
                    }
                }
                ?>
            </ul>
        </div>
    </nav>
</div>
