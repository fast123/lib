<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Application;

$autoUniqIb = $arParams['IBLOCK_ID'];
$arChangedKeys = array();
foreach ($arResult["ITEMS"] as $key => $arItem)
{
    //New value for price current and default
    if($arItem["CODE"] == "Price")
    {
        $inputPriceMin = !empty($arItem["VALUES"]["MIN"]["HTML_VALUE"])? $arItem["VALUES"]["MIN"]["HTML_VALUE"]:$arItem["VALUES"]["MIN"]["VALUE"];
        $inputPriceMax = !empty($arItem["VALUES"]["MAX"]["HTML_VALUE"])? $arItem["VALUES"]["MAX"]["HTML_VALUE"]:$arItem["VALUES"]["MAX"]["VALUE"];
        $arItem["INPUT_VALUE_MIN"] = $inputPriceMin;
        $arItem["INPUT_VALUE_MAX"] = $inputPriceMax;
    }

    $arChangedKeys[$arItem["CODE"]] = $arItem;
    $arChangedKeys[$arItem["CODE"]]["key"] = $key;

}
$arResult["ITEMS_NEW"] = $arChangedKeys;

///Get sections info
$arSectionsInfo = array();
$secObj = CIBlockSection::GetList(
    array("SORT" => "ASC"),
    array(
        "IBLOCK_ID"=>$autoUniqIb,
        "ELEMENT_SUBSECTIONS" => "Y",
        "CNT_ACTIVE" => "Y",
        "DEPTH_LEVEL" => 1
    ),
    true,
    array(),
    false
);
while ($arSection  = $secObj->GetNext())
{
    $arSectionsInfo[$arSection["ID"]] = array(
        "NAME" => $arSection["NAME"],
        "URL" => $arSection["SECTION_PAGE_URL"],
        "COUNT" => $arSection["ELEMENT_CNT"]
    );
}
$arResult["SECTIONS_INFO"] = $arSectionsInfo;

$request = Application::getInstance()->getContext()->getRequest();
if($request["sort"] == "price" && $request["order"])
{
    $arResult["sort_price"] = true;
}
