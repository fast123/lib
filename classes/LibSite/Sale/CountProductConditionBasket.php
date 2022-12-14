<?php

/*
 * выводить в основных скидках, при этом проверятся будет для каждого товара в отдельности и информция будет доступно только для каждого в отдельности
$event->addEventHandler("sale", "OnCondSaleActionsControlBuildList", ["LibSite\Sale\CountProductConditionBasket", "GetControlDescr"]);
*/
/*
 * Выводить в доп правилах, при это будет отрабатывать только в корзине и оформлении закза и будет доступен масив с заказом и корзиной
$event->addEventHandler("sale", "OnCondSaleControlBuildList", ["LibSite\Sale\CountProductConditionBasket", "GetControlDescr"]);
*/

namespace LibSite\Sale;


class CountProductConditionBasket extends \CSaleCondCtrlComplex
{
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
        return array(
            'QuantityAllTypeProduct',
        );
    }
    /**
     * @return array
     */
    public static function GetControlDescr()
    {
        $description = parent::GetControlDescr();
        $description['SORT'] = 9000;
        return $description;
    }
    /**
     * отображать в следующих блоках
     * только отдельное правило, в доп условиях в других случаях работает не корректно
     * @param $arControls
     * @return array
     */
    public static function GetShowIn($arControls)
    {

        /*$ret = array(
            \CSaleActionCtrlBasketGroup::GetControlID(), //Изменить стоимость товаров в корзине
            \CSaleActionGiftCtrlGroup::GetControlID(),  //Предоставить подарок
            \CSaleActionCtrlSubGroup::GetControlID(), // в множественных подгруппах
            \CSaleActionCtrlGroup::GetControlID() //
        );*/
        //return $ret;
        /*if (!is_array($arControls))
            $arControls = array($arControls);

        return array_values(array_unique($arControls));*/

        $ret = array (
            0 => 'CondGroup',
        );
        return $ret;

    }

    /**
     * Основная группа для стека кастомных правил
     * @param $arParams
     * @return array
     */
    public static function GetControlShow($arParams)
    {

        $arControls = static::GetControls();
        $arResult = array(
            'controlId' => static::GetControlID(),
            'controlgroup' => true,
            'group' =>  true,
            'label' => 'Кастомизированные свойства',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' => array()
        );

        foreach ($arControls as &$arOneControl)
        {
            $arResult['children'][] = array(
                'controlId' => $arOneControl['ID'],
                'group' => false,
                'label' => $arOneControl['LABEL'],
                'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => array(
                    $arOneControl['PREFIX'],
                    static::GetLogicAtom($arOneControl['LOGIC']),
                    static::GetValueAtom($arOneControl['JS_VALUE'])
                )
            );
        }

        //pre($arResult);

        if (isset($arOneControl))
            unset($arOneControl);
        return $arResult;
    }
    /**
     * Создаем наши кастомные правила
     * @param bool $controlId
     * @return array|bool|mixed
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function GetControls($controlId = false)
    {
        $controlList = array(
            'QuantityAllTypeProduct'=>array(
                'ID' => 'QuantityAllTypeProduct',
                'FIELD' => 'QUANTITYALL',
                'FIELD_TYPE' => 'string', //string//double//int
                'MULTIPLE' => 'N',
                'GROUP' => 'N',
                'LABEL' => 'Общее количество товаров, по разделу',
                'PREFIX' => 'Общее количество товаров, по разделу(через запятую, без пробелов. Пример(количестов, idSection))',
                'LOGIC' => static::getLogic(array(
                    BT_COND_LOGIC_EQ,
                    BT_COND_LOGIC_NOT_EQ,
                    BT_COND_LOGIC_GR,
                    BT_COND_LOGIC_LS,
                    BT_COND_LOGIC_EGR,
                    BT_COND_LOGIC_ELS
                )),
                'JS_VALUE' => array(
                    'type' => 'input', //'type' => 'select', // 'type' => 'dialog',
                ),
                'PHP_VALUE' => ''
            ),
        );

        foreach ($controlList as &$control)
        {
            if (!isset($control['PARENT']))
                $control['PARENT'] = true;
            $control['MULTIPLE'] = 'N';
            $control['GROUP'] = 'N';
        }

        unset($control);
        if (false === $controlId)
        {
            return $controlList;
        }
        elseif (isset($controlList[$controlId]))
        {
            return $controlList[$controlId];
        }
        else
        {
            return false;
        }
    }

    /**
     * Обработка логики правил
     * @param $oneCondition
     * @param $params
     * @param $control
     * @param bool $subs
     * @return bool|mixed|string
     */
    public static function Generate($oneCondition, $params, $control, $subs = false)
    {
        $result = '';
        if (is_string($control))
        {
            $control = static::GetControls($control);
        }
        $boolError = !is_array($control);
        $values = array();
        if (!$boolError) {
            $values = static::check($oneCondition, $params, $control, false);
            $boolError = (false === $values);
        }
        if (!$boolError) {
            $type = $oneCondition['logic'];
           if ($control['ID'] === 'QuantityAllTypeProduct') {
               $result = static::getClassName() . "::checkAllQuantityProduct({$params['ORDER']}, {$params['BASKET']}, {$params['BASKET_ROW']}, '{$values['value']}', '{$type}')";
            }
        }

        return $result;
    }

    /**
     * Логика проверки соответсятвия условиям правила корзины
     * @param $arOrder
     * @param $arBasket
     * @param $row
     * @param $values
     * @param $type
     * @return bool
     */
    public static function checkAllQuantityProduct($arOrder, $arBasket, $row, $values, $type){

        $arBasket = $arOrder['BASKET_ITEMS'];
        if(empty($values) || empty($arBasket)) return false;
        $arValue = explode(',', $values);
        if(count($arValue) != 2) return false;
        $quantity = $arValue[0];
        $sectionId = $arValue[1];
        $curQuantity = 0;

        foreach ($arBasket as $arItem){
            if( !empty($arItem['CATALOG']['SECTION_ID']) && in_array($sectionId, $arItem['CATALOG']['SECTION_ID']) ){
                $curQuantity += (int)$arItem['QUANTITY'];
            }
        }


        if ($type === 'Equal'){
            if($curQuantity == $quantity){
                return true;
            }
        }elseif($type === 'Not'){
            if($curQuantity !=  $quantity){
                return true;
            }
        }elseif($type === 'Great'){
            if($curQuantity > $quantity){
                return true;
            }
        }elseif($type === 'EqGr'){
            if($curQuantity >= $quantity){
                return true;
            }
        }elseif($type === 'Less'){
            if($curQuantity < $quantity){
                return true;
            }
        }elseif($type === 'EqLs'){
            if($curQuantity <= $quantity){
                return true;
            }
        }


        return false;
    }

}
