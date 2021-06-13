<?php

namespace FourPx\CarsAvaliable;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Properties
{

    public static function setRequiredProperties($iBlockId)
    {
        Loader::includeModule('iblock');
        if(\CIBlock::GetArrayByID($iBlockId, "SECTION_PROPERTY") !== "Y") {
            $ib = new \CIBlock;
            $ib->Update($iBlockId, array("SECTION_PROPERTY" => "Y"));
        }
        $ibp = new \CIBlockProperty;
        $arrSettings = self::getPropertiesSettings();

        //спсиок существующих свойств
        $arCurrentProp = [];
        $rsProperty = \CIBlockProperty::GetList([], ['IBLOCK_ID'=>$iBlockId]);
        while ($obj = $rsProperty->GetNext()){
            $arCurrentProp[] = $obj['CODE'];
        }

        foreach ($arrSettings as $code=>$settings) {
            if(in_array($code, $arCurrentProp)) continue;
            $settings["IBLOCK_ID"] = $iBlockId;
            $ibp->Add($settings);
        }
    }

   public static function getPropertiesSettings()
   {
       // S - строка,
       // N - число,
       // F - файл,
       // L - список,
       // E - привязка к элементам,
       // G - привязка к группам.
       $arrProperties = [
           "MARK_ID" => [
               "NAME" => "Марка",
               "ACTIVE" => "Y",
               "SORT" => "1",
               "CODE" => "MARK_ID",
               "PROPERTY_TYPE" => "S",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "YEAR" => [
               "NAME" => "Год",
               "ACTIVE" => "Y",
               "SORT" => "10",
               "CODE" => "YEAR",
               "PROPERTY_TYPE" => "N",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "RUN" => [
               "NAME" => "Пробег",
               "ACTIVE" => "Y",
               "SORT" => "20",
               "CODE" => "RUN",
               "PROPERTY_TYPE" => "N",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "PRICE" => [
               "NAME" => "Цена продажи",
               "ACTIVE" => "Y",
               "SORT" => "30",
               "CODE" => "PRICE",
               "PROPERTY_TYPE" => "N",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "TRANSMISSION" => [
               "NAME" => "Тип КПП",
               "ACTIVE" => "Y",
               "SORT" => "40",
               "CODE" => "TRANSMISSION",
               "PROPERTY_TYPE" => "S",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "FUEL" => [
               "NAME" => "Тип двигателя",
               "ACTIVE" => "Y",
               "SORT" => "50",
               "CODE" => "FUEL",
               "PROPERTY_TYPE" => "S",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "HORSE_POWER" => [
               "NAME" => "Мощность двигателя",
               "ACTIVE" => "Y",
               "SORT" => "60",
               "CODE" => "HORSE_POWER",
               "PROPERTY_TYPE" => "N",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "BODY_TYPE" => [
               "NAME" => "Тип кузова",
               "ACTIVE" => "Y",
               "SORT" => "70",
               "CODE" => "BODY_TYPE",
               "PROPERTY_TYPE" => "S",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "DRIVE" => [
               "NAME" => "Привод",
               "ACTIVE" => "Y",
               "SORT" => "80",
               "CODE" => "DRIVE",
               "PROPERTY_TYPE" => "S",
               "MULTIPLE" => "N",
               "SMART_FILTER"  => "Y",
           ],
           "HASH" => [
               "NAME" => "Hash",
               "ACTIVE" => "Y",
               "SORT" => "10000",
               "CODE" => "HASH",
               "PROPERTY_TYPE" => "S",
               "MULTIPLE" => "N",
           ],
           "IMAGES" => [
               "NAME" => "Картинки",
               "ACTIVE" => "Y",
               "SORT" => "1000",
               "CODE" => "IMAGES",
               "PROPERTY_TYPE" => "F",
               "MULTIPLE" => "Y",
           ],
       ];
       return $arrProperties;
   }

}