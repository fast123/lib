<?php


namespace LibSite\Delivery;


/**
 * Class TestRestriction пример добавления ограничения на доставку
 *
 * @package LibSite\Delivery
 */
class TestRestriction extends \Bitrix\Sale\Delivery\Restrictions\Base
{
    /**
     * Установка заголовока
     * @return string
     * @throws NotImplementedException
     */
    public static function getClassTitle()
    {
        return 'test';
    }

    /**
     * Установка описания
     * @return string
     * @throws NotImplementedException
     */
    public static function getClassDescription()
    {
        return 'test2';
    }

    /**
     * Подготовить необходимые данные для проверки ограничения и вернуть их. Далее эти данные передаются в метод check
     * если доп анные получать не надо вернуть null
     * @param Entity $entity
     * @return mixed
     * @throws NotImplementedException
     */
    protected static function extractParams(Bitrix\Sale\Shipment $shipment)
    {
        $someShipmentParams = array();

        // Получаем товары в корзине:
        foreach ($shipment->getShipmentItemCollection() as $shipmentItem) {
            /** @var \Bitrix\Sale\BasketItem $basketItem - запись в корзине*/
            $basketItem = $shipmentItem->getBasketItem();
        }

        // Получаем информацию о заказе:
        /** @var \Bitrix\Sale\ShipmentCollection $collection - коллекция всех отгрузок в заказе */
        $collection = $shipment->getCollection();
        /** @var \Bitrix\Sale\Order $order - объект заказа*/
        $order = $collection->getOrder();

        // Получаем выбранные оплаты:
        /** @var \Bitrix\Sale\Payment $payment - объект оплаты */
        foreach($order->getPaymentCollection() as $payment) {
            /** @var int $paySystemId - ID способа оплаты*/
            $paySystemId = $payment->getPaymentSystemId();
            // ...
            $someShipmentParams["paySystem"] = $paySystemId;
        }

        return $someShipmentParams;
    }

    /**
     * Должен возвращать массив параметров ограничения, для вывода в админке
     * Если ограничения не требует настройки, то возвращается пустой массив
     * @return array
     */
    public static function getParamsStructure($deliveryId = 0)
    {
        return array(
            "MY_PARAM_CHECKBOX" => array(
                'TYPE' => 'Y/N',
                'VALUE' => 'Y',
                'LABEL' => 'Галочка',
            ),
            "MY_PARAM_ENUM" => array(
                "TYPE" => "ENUM",
                'MULTIPLE' => 'Y',
                "OPTIONS" => array(1 => "Первый вариант", 2 => "Второй вариант"),
                "LABEL" => 'Список',
            ),
            "MY_PARAM_NUMBER" => array(
                'TYPE' => 'NUMBER',
                'DEFAULT' => "0",
                'MIN' => 0,
                'LABEL' => 'Число',
            ),
        );
    }

    /**
     * Определяет доступна ли доставка при данных параметрах заказа
     *  Возвращает true, если данная служба доставки доступна
     * @param mixed $shipmentParams араметры отгрузки, которые вернул метод extractParams
     * @param array $restrictionParams параметры ограничения, которые ввели в админке благодоря методу getParamsStructure
     * @param int $serviceId  id службы доставки
     * @return bool
     * @throws NotImplementedException
     */
    public static function check($shipmentParams, array $restrictionParams, $deliveryId = 0)
    {
        return true;
    }
}