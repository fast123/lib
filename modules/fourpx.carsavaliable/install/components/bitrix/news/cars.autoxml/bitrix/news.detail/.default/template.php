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
use Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs($templateFolder. "/fotorama/fotorama.js");
Asset::getInstance()->addCss($templateFolder . "/fotorama/fotorama.css");

$this->setFrameMode(true);
?>
<div class="tradein-detail">
    <div class="bordered">
        <div class="clearfix">
            <h1 class="tradein-detail-title">
                <?=$arResult["NAME"]?>
            </h1>
            <div class="tradein-detail-price">
            <span>
                <?= number_format(($arResult['PROPERTIES']['PRICE']['VALUE']), 0, '', ' ') ?>
            </span>
                руб.
            </div>
        </div>

        <div class="tradein-detail-top clearfix">
            <div class="tradein-detail-slider">
                <div class="tradein-detail-slider-for fotorama"
                     data-allowfullscreen="true"
                     data-nav="thumbs"
                     data-arrows="true"
                     data-swipe="true"
                     data-trackpad="true"
                     data-hash="true"
                >
                    <? if (!empty($arResult['PROPERTIES']['IMAGES']["VALUE"])):?>
                        <? foreach ($arResult['PROPERTIES']['IMAGES']["VALUE"] as $index => $photo):?>
                            <?$src = CFile::GetPath($photo)?>
                            <a href="<?=$src?>" data-full="<?=$src?>">
                                <img src="<?=$src?>" alt="Фото <?=$index?>" title="Фото <?=$index?>" data-full="<?=$src?>">
                            </a>
                        <? endforeach; ?>
                    <?endif;?>
                </div>
                <div style="clear: both"></div>
            </div>
            <div class="tradein-detail-right-part">
                <div class="tradein-detail-options-table-cnt">
                    <table class="tradein-detail-options-table">
                        <tr>
                            <td>Год выпуска</td>
                            <td><?=$arResult["PROPERTIES"]["YEAR"]["VALUE"]?> г.</td>
                        </tr>
                        <tr>
                            <td>Пробег</td>
                            <td>
                                <?= number_format($arResult["PROPERTIES"]["RUN"]["VALUE"], 0, "", " "); ?>
                                км
                            </td>
                        </tr>
                        <tr>
                            <td>Площадка</td>
                            <td class="place">
                                <?=$arResult["PROPERTIES"]["DEALER_NAME"]["VALUE"]?>
                            </td>
                        </tr>
                        <tr>
                            <td>ID</td>
                            <td><?=$arResult["PROPERTIES"]["UNIQUE_ID"]["VALUE"]?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- end of .tradein-detail-top -->
        <div class="tradein-detail-block tradein-detail-characteristics clearfix">
            <h4 class="tradein-detail-block-title">Характеристики</h4>

            <table class="first-table">
                <tr>
                    <td>Марка</td>
                    <td><?=$arResult["PROPERTIES"]["MARK_ID"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Модель</td>
                    <td><?=$arResult["PROPERTIES"]["FOLDER_ID"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Год выпуска</td>
                    <td><?=$arResult["PROPERTIES"]["YEAR"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Цена</td>
                    <td><?= number_format(($arResult["PROPERTIES"]["PRICE"]["VALUE"]), 0, '', ' ') ?> руб.</td>
                </tr>
                <tr>
                    <td>Пробег</td>
                    <td><?= number_format($arResult["PROPERTIES"]["RUN"]["VALUE"], 0, "", " "); ?></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>Объем двигателя</td>
                    <td><?=$arResult["PROPERTIES"]["VOLUME"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Мощность двигателя</td>
                    <td><?=$arResult["PROPERTIES"]["HORSE_POWER"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Тип двигателя</td>
                    <td><?=$arResult["PROPERTIES"]["FUEL"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Тип кузова</td>
                    <td><?=$arResult["PROPERTIES"]["BODY_TYPE"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>КПП</td>
                    <td><?=$arResult["PROPERTIES"]["TRANSMISSION"]["VALUE"]?></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>Привод</td>
                    <td><?=$arResult["PROPERTIES"]["DRIVE"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Цвет</td>
                    <td><?=$arResult["PROPERTIES"]["COLOR"]["VALUE"]?></td>
                </tr>
                <tr>
                    <td>Положение руля</td>
                    <td><?=$arResult["PROPERTIES"]["WHEEL"]["VALUE"]?></td>
                </tr>
            </table>
        </div>

        <div class="tradein-detail-block tradein-detail-complectation">
            <h4 class="tradein-detail-block-title">
                Комплектация
            </h4>
            <div class="tradein-detail-block-toggle clearfix">
                <?
                $option = explode(',', $arResult["PROPERTIES"]["EXTRAS"]["VALUE"]);
                $optionCount = count($option);
                $optionColumn = $optionCount / 3;
                if ($optionCount % 3 != 0)
                    $optionColumn++;
                ?>
                <? foreach (array_chunk($option, $optionColumn) as $arChunk): ?>
                    <ul>
                        <? foreach ($arChunk as $item): ?>
                            <li><i class="fa fa-check" aria-hidden="true"></i> <?= $item ?></li>
                        <? endforeach; ?>
                    </ul>
                <? endforeach; ?>
            </div>
        </div>
        <div class="tradein-detail-block tradein-detail-description">
            <h4 class="tradein-detail-block-title">Описание</h4>
            <div class="tradein-detail-block-toggle"><?=$arResult["DETAIL_TEXT"];?></div>
        </div>
    </div>
</div>

