<?php

namespace LibSite\Delivery;

class RestrictionBuild
{
    /**
     * Событие при построении списка ограничений службы доставки системы (нужно добавить ограничение в масив)
     *
     * @return [type] [description]
     */

    /*
     * Не забыть объявить в init.php
     * Loader::registerAutoLoadClasses(
            null,
            [
                'LibSite\Delivery\RestrictionBuild' => '/local/php_interface/LibSite/Delivery/RestrictionBuild.php',
                'LibSite\Delivery\TestRestriction' => '/local/php_interface/LibSite/Delivery/TestRestriction.php',
            ]
        );

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('sale', 'onSaleDeliveryRestrictionsClassNamesBuildList', ['LibSite\Delivery\RestrictionBuild', 'classNamesBuildList']);
     *
     * */
    public static function classNamesBuildList(){
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                'LibSite\Delivery\TestRestriction' => '/local/php_interface/LibSite/Delivery/TestRestriction.php',
            )
        );
    }
}