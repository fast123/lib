<?php

namespace Shmt\Delivery;


/**
 * Class Test пример добавления обработчика доставки
 *
 *
 */
class Test extends \Bitrix\Sale\Delivery\Services\Base
{

    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = false;
    protected static $canHasProfiles = true;//если нужны профили

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    /**
     * Заголовок доставки
     * @return string
     */
    public static function getClassTitle()
    {
        return 'Test Delivery';
    }

    /**
     * Описание доставки
     * @return string
     */
    public static function getClassDescription()
    {
        return 'Test Delivery description';
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    /**
     * @return bool If admin could edit extra services
     */
    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    /**
     * Формирование вкладок с полями (настройки)
     * @return array
     * @throws \Exception
     */
    protected function getConfigStructure()
    {
        $result = array(
            'MAIN' => array(
                'TITLE' => 'Основные',
                'DESCRIPTION' => 'Основные настройки',
                'ITEMS' => array(
                    'API_KEY' => array(
                        'TYPE' => 'STRING',
                        'NAME' => 'Ключ API',
                    ),
                    'TEST_MODE' => array(
                        'TYPE' => 'Y/N',
                        'NAME' => 'Тестовый режим',
                        'DEFAULT' => 'N'
                    ),
                    'PACKAGING_TYPE' => array(
                        'TYPE' => 'ENUM',
                        'NAME' => 'Тип упаковки',
                        'DEFAULT' => 'BOX',
                        'OPTIONS' => array(
                            'BOX' => 'Коробка',
                            'ENV' => 'Конверт',
                        )
                    ),
                )
            ),
            'MAIN2' => array(
                'TITLE' => 'Основные2',
                'DESCRIPTION' => 'Основные настройки',
                'ITEMS' => array(
                    'API_KEY' => array(
                        'TYPE' => 'STRING',
                        'NAME' => 'Ключ API',
                    ),
                    'TEST_MODE' => array(
                        'TYPE' => 'Y/N',
                        'NAME' => 'Тестовый режим',
                        'DEFAULT' => 'N'
                    ),
                    'PACKAGING_TYPE' => array(
                        'TYPE' => 'ENUM',
                        'NAME' => 'Тип упаковки',
                        'DEFAULT' => 'BOX',
                        'OPTIONS' => array(
                            'BOX' => 'Коробка',
                            'ENV' => 'Конверт',
                        )
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


        //конец


        //Если используются профили во избежание случайных ошибок, при использовании профилей в методе расчёта
        // родительского сервиса стоит вызываем исключение
        //throw new \Bitrix\Main\SystemException('Only profiles can calculate concrete');

        $result = new \Bitrix\Sale\Delivery\CalculationResult();


        //установка  цены
        $result->setDeliveryPrice(
            roundEx(
                500,
                SALE_VALUE_PRECISION
            )
        );

        //пример вывода ошибки
        //$result->addError(new Bitrix\Main\Error("Данный сервис недоступен для выбранного местоположения"));

        //установка времени доставки, текст произвольный
        $result->setPeriodDescription('4-7 days');

        return $result;
    }


    /**
     * Устанавливаем флаг вывода профилей
     * @return bool
     */
    public static function canHasProfiles()
    {
        return self::$canHasProfiles;
    }

    /**
     * Указаывает что профили наследники класа
     * @return string[]
     */
    public static function getChildrenClassNames()
    {
        return array(
            'Shmt\Delivery\TestProfile'
        );
    }

    /**
     * Название профилей,
     *
     * @return string[]
     */
    public function getProfilesList()
    {
        return array("Новый профиль", "test");
    }


}