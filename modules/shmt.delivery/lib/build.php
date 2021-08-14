<?php

namespace Shmt\Delivery;

class Build
{
    protected static $urlLib = '/local/modules/shmt.delivery/lib/';
    /**
     * Событие при построении списка ограничений службы доставки системы (нужно добавить ограничение в масив)
     * @return [type] [description]
     */
    public static function classNamesBuildList(){
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                'Shmt\Delivery\Test' => self::$urlLib.'test.php',
                'Shmt\Delivery\TestProfile' => self::$urlLib.'testprofile.php',
            )
        );
    }
}