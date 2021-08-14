<?php


namespace LibSite\Sale;

use \Bitrix\Main\Loader;

/**
 * Class CustomConditionBasket
 *  Добавляет новое действие в правила работы корзины для Маркетинга
 * @package LibSite\Sale
 */

/*
 * Подключение в init
$eventManager->addEventHandler(
    "sale",
    "OnCondSaleActionsControlBuildList",
    ["LibSite\Sale\CustomConditionBasket", "GetControlDescr"]
);
 */
class CustomConditionBasket extends \CSaleActionCtrlAction
{

    public static function GetControlDescr() {
        $description = parent::GetControlDescr();
        $description['EXECUTE_MODULE'] = 'all';//Для сохранения в таблицу
        $description['SORT'] = 500;

        return $description;
    }

    /**
     * Получение имени класса
     * @return string
     */
    public static function GetClassName()
    {
        return __CLASS__;
    }

    /**
     * Получение ID условия
     * @return array|string
     */
    public static function GetControlID()
    {
        return "DiscountPriceType";//Уникальный идентификатор
    }

    /**
     * Добавление пункта в список условий с указанием отдельной группы
     * @param $arParams
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function GetControlShow($arParams)
    {
        $arControls = static::GetAtomsEx();
        $arResult = array(
            'controlgroup' => true,
            'group' =>  false,
            'label' => 'Кастомные правила',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' =>  [array(
                'controlId' => static::GetControlID(),
                'group' => false,
                'label' => "Если тип цены не",
                'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => array(
                    "Если тип цены не",
                    $arControls["PT"]
                )
            )]
        );

        return $arResult;
    }

    /**
     * Формирование данных для визуального представления условия
     * @param bool $strControlID
     * @param bool $boolEx
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {
        $boolEx = (true === $boolEx ? true : false);
        $priceList = [];
        if (Loader::includeModule('main')) {
            // Получение типов цен
            $arGroupPrice = \Bitrix\Catalog\GroupTable::getList(
                [
                    'select' => ['ID', 'NAME'],
                    'cache' => [
                        'ttl' => 60,
                        'cache_joins' => true,
                    ]
                ]
            );

            while ($el = $arGroupPrice->fetch()) {
                $priceList[$el['ID']] = $el['NAME'] . " [" . $el['ID'] . "]";
            }
        }

        $arAtomList = [
            "PT" => [
                "JS" => [
                    "id" => "PT",
                    "name" => "extra",
                    "type" => "select",
                    "values" => $priceList,
                    "defaultText" => "...",
                    "defaultValue" => "",
                    "first_option" => "..."
                ],
                "ATOM" => [
                    "ID" => "PT",
                    "FIELD_TYPE" => "string",
                    "FIELD_LENGTH" => 255,
                    "MULTIPLE" => "N",
                    "VALIDATE" => "list"
                ]
            ],
        ];
        if (!$boolEx) {
            foreach ($arAtomList as &$arOneAtom)
            {
                $arOneAtom = $arOneAtom["JS"];
            }
            if (isset($arOneAtom))
            {
                unset($arOneAtom);
            }
        }

        return $arAtomList;
    }

    /**
     * Функция должна вернуть колбэк того, что должно быть выполнено при наступлении условий
     * @param $arOneCondition
     * @param $arParams
     * @param $arControl
     * @param bool $arSubs
     * @return string
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
    {
        return __CLASS__ . '::applyProductDiscount($row,' . $arOneCondition["PT"] . ')';
    }

    /**
     * Логика кастомного условия
     * @param $row
     * @param $priceType
     * @return bool
     */
    public static function applyProductDiscount($row, $priceType)
    {
        return $priceType != $row['PRICE_TYPE_ID'];
    }
}