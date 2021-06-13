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

$this->addExternalCss("/bitrix/css/main/bootstrap.css");
$this->addExternalCss("/bitrix/css/main/font-awesome.css");
$arResult["FORM_ACTION"] = (!empty($arParams['FORM_ACTION'])) ? $arParams['FORM_ACTION'] : $arResult["FORM_ACTION"];
?>
<div class="bx-filter bx-yellow">
	<div class="bx-filter-section container-fluid">
		<div class="row">
            <div class="col-lg-12 bx-filter-title">
                <?echo GetMessage("CT_BCSF_FILTER_TITLE")?>
            </div>
        </div>
		<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="smartfilter">
			<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
			<?endforeach;?>
			<div class="row">

                <?foreach($arResult["ITEMS"] as $key=>$arItem):?>
                    <?if($arItem["CODE"] !== 'MARK_ID') continue;?>
                    <div class="col-lg-12 bx-filter-parameters-box bx-active">
                        <span class="bx-filter-container-modef"></span>
                        <div class="bx-filter-block" data-role="bx_filter_block">
                            <div class="row bx-filter-parameters-box-container ">
                                <div class="col-xs-12 container-top">
                                    <?foreach($arItem["VALUES"] as $val => $ar):?>
                                        <div class="checkbox">
                                            <label data-role="label_<?=$ar["CONTROL_ID"]?>"
                                                   class="bx-filter-param-label <? echo $ar["DISABLED"] ? 'disabled': '' ?>"
                                                   for="<? echo $ar["CONTROL_ID"] ?>">
                                                <span class="bx-filter-input-checkbox">
                                                    <input type="checkbox"
                                                           value="<? echo $ar["HTML_VALUE"] ?>"
                                                           name="<? echo $ar["CONTROL_NAME"] ?>"
                                                           id="<? echo $ar["CONTROL_ID"] ?>"
                                                        <? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
                                                        onclick="smartFilter.click(this)"
                                                    />
                                                    <span class="bx-filter-param-text" title="<?=$ar["VALUE"];?>"><?=$ar["VALUE"];?>
                                                        <?if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"])):?>
                                                            <span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>
                                                        <?endif;?>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    <?endforeach;?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?endforeach;?>

                <div class="col-lg-12 bx-filter-parameters-box">
                    <span class="bx-filter-container-modef"></span>
                    <div class="bx-filter-parameters-box-title title-toggle" onclick="smartFilter.hideFilterProps(this)">
                            <span>другие параметры</span>
                    </div>
                    <div class="bx-filter-block" data-role="bx_filter_block">
                        <div class="row bx-filter-parameters-box-container">
                            <?foreach($arResult["ITEMS"] as $key=>$arItem) {
                                if(empty($arItem["VALUES"]) || isset($arItem["PRICE"])) continue;
                                if ($arItem["DISPLAY_TYPE"] == "A" && ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)) continue;
                                if($arItem["CODE"] === 'MARK_ID') continue;
                                $arCur = current($arItem["VALUES"]);
                                ?>
                                <div class="col-xs-12">
                                    <div class="bx-filter-parameter-title">
                                        <?=$arItem["NAME"]?>:
                                    </div>
                                </div>
                                <?
                                switch ($arItem["DISPLAY_TYPE"])
                                {
                                    case "A"://NUMBERS
                                        ?>
                                    <?if(in_array($arItem["CODE"] , $arResult['ARR_SELECT_FIELD'])):
                                        $arrMin = $arResult["ARR_MIN_" . $arItem["CODE"]];
                                        $arrMax = $arResult["ARR_MAX_" . $arItem["CODE"]];
                                        ?>
                                        <div class="col-xs-6 bx-filter-parameters-box-container-block bx-left">
                                            <div class="bx-filter-input-container">
                                                <select class="min-price" id="<?=$arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
                                                        name="<?=$arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>">
                                                    <option value="" selected="" disabled=""></option>
                                                    <?foreach ($arrMin as $value):?>
                                                        <option value="<?=$value?>"<?=($arItem["VALUES"]["MIN"]["HTML_VALUE"] == $value) ? 'selected' : ''?>>
                                                            <?=$value?>
                                                        </option>
                                                    <?endforeach;?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xs-6 bx-filter-parameters-box-container-block bx-right">
                                            <div class="bx-filter-input-container">
                                                <select class="max-price" id="<?=$arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
                                                        name="<?=$arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>">
                                                    <option value="" selected="" disabled=""></option>
                                                    <?foreach ($arrMax as $value):?>
                                                        <option value="<?=$value?>"
                                                            <?=($arItem["VALUES"]["MAX"]["HTML_VALUE"] == $value) ? 'selected' : ''?>>
                                                            <?=$value?>
                                                        </option>
                                                    <?endforeach;?>
                                                </select>
                                            </div>
                                        </div>
                                    <?else:?>
                                        <div class="col-xs-6 bx-filter-parameters-box-container-block bx-left">
                                            <div class="bx-filter-input-container">
                                                <input
                                                    class="min-price"
                                                    type="text"
                                                    name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
                                                    id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
                                                    value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
                                                    size="5"
                                                    onkeyup="smartFilter.keyup(this)"
                                                    />
                                            </div>
                                        </div>
                                        <div class="col-xs-6 bx-filter-parameters-box-container-block bx-right">
                                            <div class="bx-filter-input-container">
                                                <input
                                                    class="max-price"
                                                    type="text"
                                                    name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
                                                    id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
                                                    value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
                                                    size="5"
                                                    onkeyup="smartFilter.keyup(this)"
                                                    />
                                            </div>
                                        </div>
                                    <?endif;?>
                                        <?
                                        break;
                                    default://CHECKBOXES
                                        ?>
                                        <div class="col-xs-12 checkbox-container">
                                            <?foreach($arItem["VALUES"] as $val => $ar):?>
                                                <div class="checkbox">
                                                    <label data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx-filter-param-label <? echo $ar["DISABLED"] ? 'disabled': '' ?>" for="<? echo $ar["CONTROL_ID"] ?>">
                                                        <span class="bx-filter-input-checkbox">
                                                            <input
                                                                class="bx-filter-checkbox"
                                                                type="checkbox"
                                                                value="<? echo $ar["HTML_VALUE"] ?>"
                                                                name="<? echo $ar["CONTROL_NAME"] ?>"
                                                                id="<? echo $ar["CONTROL_ID"] ?>"
                                                                <? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
                                                                onclick="smartFilter.click(this)"
                                                            />
                                                            <span class="bx-filter-param-text" title="<?=$ar["VALUE"];?>">
                                                                <span><?=$ar["VALUE"];?></span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                            <?endforeach;?>
                                        </div>
                                <?
                                }
                                ?>
                            <?}?>
                        </div>
                        <div style="clear: both"></div>
                    </div>
                </div>

			</div>
            <!--//row-->
			<div class="row">
				<div class="col-xs-12 bx-filter-button-box">
					<div class="bx-filter-block">
						<div class="bx-filter-parameters-box-container">
							<input
								class="btn btn-themes"
								type="submit"
								id="set_filter"
								name="set_filter"
								value="<?=GetMessage("CT_BCSF_SET_FILTER")?>"
							/>
						</div>
					</div>
				</div>
			</div>
			<div class="clb"></div>
		</form>
	</div>
</div>
<script type="text/javascript">
	var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=CUtil::JSEscape($arParams["FILTER_VIEW_MODE"])?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
</script>