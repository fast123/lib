<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$APPLICATION->SetAdditionalCSS("/bitrix/css/main/font-awesome.css");
?>

<div class="cars-list clearfix">

    <div class="filter_block">
        <span class="filter_header">Сортировать по:</span>
        <ul class="filter_type">
            <? $sort = ($_GET["method"] == "asc") ? "active-sort-o" : "active-sort-re_o" ?>
            <li class="filter_list_item <?= ($_GET["sort"] == "property_PRICE") ? "active-sort " . $sort : "" ?>">
                <a href="?sort=property_PRICE&method=<?= ($_GET["method"] == "asc") ? "desc" : "asc" ?>">
                    цене
                </a>
            </li>
            <li class="filter_list_item <?= ($_GET["sort"] == "property_RUN") ? "active-sort " . $sort : "" ?>">
                <a href="?sort=property_RUN&method=<?= ($_GET["method"] == "asc") ? "desc" : "asc" ?>">
                    пробегу
                </a>
            </li>
            <li class="filter_list_item <?= ($_GET["sort"] == "property_YEAR") ? "active-sort " . $sort : "" ?>">
                <a href="?sort=property_YEAR&method=<?= ($_GET["method"] == "asc") ? "desc" : "asc" ?>">
                    году выпуска
                </a>
            </li>
        </ul>
    </div>

    <div class="cars-list-box">
        <? foreach ($arResult['ITEMS'] as $i): ?>
                    <div class="list__item">
                        <span class="list__title">
                            <a href="<?=$i["DETAIL_PAGE_URL"]?>" style="text-decoration: none;">
                                <?= $i['NAME'] ?>
                            </a>
                        </span>
                        <div class="list__image">
                            <a class="list__link" href="<?=$i["DETAIL_PAGE_URL"]?>">
                                <img class="car-image" src="<?=$i["PREVIEW_PICTURE"]["SRC"]?>" alt="<?= $i['NAME'] ?>"/>
                                <span class="list__description">
                                    <?= $i["PROPERTIES"]["YEAR"]["VALUE"] ?> год,
                                    <?= $i["PROPERTIES"]["VOLUME"]["VALUE"] ?> л,
                                    <?= $i["PROPERTIES"]["TRANSMISSION"]["VALUE"] ?>, <br>
                                    <?= $i["PROPERTIES"]["RUN"]["VALUE"] ?> км.
                                </span>
                            </a>
                        </div>
                        <a href="<?=$i["DETAIL_PAGE_URL"]?>" class="cert_programm_cont" style="text-decoration: none; display: block;">
                            <div class="list__price">
                                <span class="current-price">
                                    <?= number_format($i["PROPERTIES"]["PRICE"]["VALUE"], 0, '', ' ') ?> руб.
                                </span>
                            </div>
                        </a>
                    </div>
        <? endforeach; ?>
    </div>
    <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
        <?= $arResult["NAV_STRING"] ?>
    <? endif; ?>
</div>