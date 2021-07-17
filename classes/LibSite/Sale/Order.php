<?php

namespace LibSite\Sale;

use \Bitrix\Sale\Order as COrder;

class Order
{

    /*
     *  Установить значения свойств заказа
     *
     *
     */
    public static function setOrderProps($orderId = null, $arSetProps = [])
    {
        $result = false;

        if (($order = COrder::load($orderId)) instanceof COrder) {

            $orderPropsCollection = $order->getPropertyCollection();

            $arOrderProps = $orderPropsCollection->getArray()['properties'];

            foreach ($arOrderProps as $prop) {
                $propCode = $prop['CODE'];

                if ($arSetProps[$propCode]) {
                    $propertyId = $prop['ID'];
                    $newValue = $arSetProps[$propCode];

                    $orderPropsCollection->getItemByOrderPropertyId($propertyId)->setValue($newValue);

                    unset($arSetProps[$propCode]);

                    $result = true;
                }
            }

            $order->save();
        }

        return $result;
    }


    /*
     * Получения свойств заказа
     */

    public static function getOrderProps($orderId = null, $arPropsList = [])
    {
        $result = false;

        if ($orderId > 0 && ($order = COrder::load($orderId)) instanceof COrder) {

            $orderPropsCollection = $order->getPropertyCollection();

            $arOrderProps = $orderPropsCollection->getArray()['properties'];

            foreach ($arOrderProps as $prop) {
                $propCode = $prop['CODE'];

                if (in_array($propCode, $arPropsList) || empty($arPropsList)) {
                    $property = $orderPropsCollection->getItemByOrderPropertyId($prop['ID']);
                    $result[$propCode] = $property->getValue();
                }
            }
        }

        return $result;
    }



}
