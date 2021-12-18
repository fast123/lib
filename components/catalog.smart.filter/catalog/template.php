<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
?>
<div class="catalog__filter js-filter">
    <div class="catalog__filter-inner js-filter-container">
        <div class="catalog__filter-wrapper js-filter-wrapper">
            <form action="<?=$arResult['FILTER_AJAX_URL']?>" class="filter js-filter-inner js-smartfilter">
                <div class="filter__control js-filter-line">
                    <div class="filter__control-line"></div>
                </div>
                <div class="filter__inner">
                    <?foreach ($arResult['ITEMS'] as $arItem):?>
                        <?if($arItem['PROPERTY_TYPE']=='N'):?>
                            <div class="filter__item js-filter-item">
                                <div class="filter__row js-filter-row <?if($arItem['DISPLAY_EXPANDED']=='Y'):?>active<?endif?>">
                                    <div class="filter__name"><?=$arItem['NAME']?></div>
                                    <div class="filter__arrow">
                                        <svg>
                                            <use xlink:href="<?=ASSETS_FOLDER?>/img/sprites/sprite.svg#icon_filter_down"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="filter__group js-filter-group"
                                     <?if($arItem['DISPLAY_EXPANDED']=='Y'):?>style="display: block"<?endif?>>
                                    <div class="filter__range">
                                        <div class="filter__input">
                                            <input type="text"
                                                   class="input-text input-text_border"
                                                   placeholder="От"
                                                   name="<?=$arItem['VALUES']['MIN']['CONTROL_NAME']?>"
                                                   value="<?=$arItem['VALUES']['MIN']["VALUE"]?>"
                                            >
                                        </div>
                                        <div class="filter__input">
                                            <input type="text"
                                                   class="input-text input-text_border"
                                                   placeholder="До"
                                                   name="<?=$arItem['VALUES']['MAX']['CONTROL_NAME']?>"
                                                   value="<?=$arItem['VALUES']['MAX']["VALUE"]?>"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?continue?>
                        <?endif?>
                        <?if($arItem['CODE']=='color_hex'):?>
                            <div class="filter__item js-filter-item">
                                <div class="filter__row js-filter-row <?if($arItem['DISPLAY_EXPANDED']=='Y'):?>active<?endif?>">
                                    <div class="filter__name"><?=$arItem['NAME']?></div>
                                    <div class="filter__arrow">
                                        <svg>
                                            <use xlink:href="<?=ASSETS_FOLDER?>/img/sprites/sprite.svg#icon_filter_down"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="filter__group js-filter-group"
                                     <?if($arItem['DISPLAY_EXPANDED']=='Y'):?>style="display: block"<?endif?>>
                                    <div class="filter__colors">
                                        <?foreach ($arItem['VALUES'] as $val):?>
                                            <?if($val['DISABLED']) continue?>
                                            <div class="filter__color">
                                                <label class="input-color" style="color:<?=$val['VALUE']?>">
                                                    <input type="radio"
                                                           name="<?=$val['CONTROL_NAME_ALT']?>"
                                                           value="<?=$val["HTML_VALUE_ALT"]?>"
                                                           <?if($val['CHECKED']):?>checked<?endif?>
                                                    >
                                                    <span></span>
                                                </label>
                                            </div>
                                        <?endforeach?>
                                    </div>
                                </div>
                            </div>
                            <?continue?>
                        <?endif?>
                        <div class="filter__item js-filter-item">
                            <div class="filter__row js-filter-row <?if($arItem['DISPLAY_EXPANDED']=='Y'):?>active<?endif?>">
                                <div class="filter__name"><?=$arItem['NAME']?></div>
                                <div class="filter__arrow">
                                    <svg>
                                        <use xlink:href="<?=ASSETS_FOLDER?>/img/sprites/sprite.svg#icon_filter_down"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="filter__group js-filter-group"
                                 <?if($arItem['DISPLAY_EXPANDED']=='Y'):?>style="display: block"<?endif?>>
                                <div class="filter__group-inner">
                                    <?foreach ($arItem['VALUES'] as $val):?>
                                        <?if($val['DISABLED'] && $arItem['CODE']!='mark_id') continue?>
                                        <label class="input-checkbox"
                                            <?if($val['DISABLED'] && $arItem['CODE']=='mark_id'):?>
                                                style="opacity: 0.5"
                                            <?endif?>
                                        >
                                            <input type="checkbox"
                                               name="<?=$val['CONTROL_NAME']?>"
                                               class="input-checkbox__input"
                                               value="<?=$val["HTML_VALUE"]?>"
                                               <?if($val['CHECKED']):?>checked<?endif?>
                                                <?if($val['DISABLED'] && $arItem['CODE']=='mark_id'):?>
                                                    disabled
                                                <?endif?>
                                            >
                                            <span><?=$val['VALUE']?></span>
                                        </label>
                                    <?endforeach?>
                                </div>
                            </div>
                        </div>
                    <?endforeach?>

                </div>
                <div class="filter__buttons">
                    <div class="filter__button">
                        <button type="submit" class="button"><span>Применить</span></button>
                    </div>
                    <div class="filter__button">
                        <div class="button button_empty js-clear-filter"><span>Сбросить</span></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
