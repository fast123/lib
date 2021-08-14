<?php

namespace Shmt\Delivery;


/**
 * Класс обработчик прфоиля доставки
 *
 * @package Shmt\Delivery
 */
class TestProfile extends \Bitrix\Sale\Delivery\Services\Base
{
    protected static $isProfile = true;
    protected static $parent = null;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->parent = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($this->parentId);
    }

    /**
     * Заголовок профиля доставки
     * @return string
     */
    public static function getClassTitle()
    {
        return 'Yet Another Delivery profile';
    }

    /**
     * Описание профиля доставки
     * @return string
     */
    public static function getClassDescription()
    {
        return 'My custom handler for Yet Another Delivery Service profile';
    }

    /**
     * @return Base|null
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function getParentService()
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately()
    {
        return $this->getParentService()->isCalculatePriceImmediately();
    }

    /**
     * Установка что класс является обработчиком профиля
     * @return bool
     */
    public static function isProfile()
    {
        return self::$isProfile;
    }

    /**
     * Формирование вкладок с полями (настройки)
     * @return array
     * @throws \Exception
     */
    protected function getConfigStructure()
    {
        $result = array(
            "MAIN" => array(
                'TITLE' => 'Основные',
                'DESCRIPTION' => 'Основные настройки',
                'ITEMS' => array(
                    'TARIFF_ID' => array(
                        "TYPE" => 'STRING',
                        "NAME" => 'ID тарифа службы доставки',
                    ),
                )
            )
        );
        return $result;
    }

    /**
     * Расчет, получение, установка цены и сроков доставик
     * @param \Bitrix\Sale\Shipment $shipment.
     * @return Delivery\CalculationResult
     */
    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment = null)
    {
        // Какие-то действия по получению стоимости и срока...
        $weight = $shipment->getWeight(); // вес отгрузки
        $order = $shipment->getCollection()->getOrder(); // заказ
        $props = $order->getPropertyCollection();
        $locationCode = $props->getDeliveryLocation()->getValue(); // местоположение

        $result = new \Bitrix\Sale\Delivery\CalculationResult();
        $result->setDeliveryPrice(
            roundEx(
                500,
                SALE_VALUE_PRECISION
            )
        );
        $result->setPeriodDescription('2-3 days');

        return $result;
    }

    /**
     * Если ошибка расчета профиль просто не выводится
     * @param \Bitrix\Sale\Shipment $shipment.
     * @return Delivery\CalculationResult
     */
    public function isCompatible(\Bitrix\Sale\Shipment $shipment)
    {
        $calcResult = self::calculateConcrete($shipment);
        return $calcResult->isSuccess();
    }
}