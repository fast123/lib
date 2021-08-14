<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Shmt\Delivery\Build;
use Shmt\Delivery\Test;
use Shmt\Delivery\TestProfile;

Loc::loadMessages(__FILE__);

/**
 * Class shmt_delivery
 *
 */
class shmt_delivery extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'shmt.delivery';
        $this->MODULE_NAME = Loc::getMessage('MODULE_DELIVERY_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DELIVERY_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('MODULE_DELIVERY_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = '';

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';

        //$this->ENTITY_NAME = '\Fourpx\CarsAvaliable\LogTable';
    }

    /**
     *  Установка модуля
     * запускается при нажатии кнопки Установить на странице Модули
     */
    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        //$test = new Test();
        $this->installEvent();
    }

    /**
     * Удалениея модуля
     * запускается при нажатии кнопки Удалить на странице Модули административного раздела, осуществляет деинсталляцию модуля.
     */
    public function doUninstall()
    {
        $this->unInstallEvent();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installEvent(){
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'sale',
            'onSaleDeliveryHandlersClassNamesBuildList',
            $this->MODULE_ID,
            '\Shmt\Delivery\Build',
            'classNamesBuildList'
        );
    }


    public function unInstallEvent(){
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'sale',
            'onSaleDeliveryHandlersClassNamesBuildList',
            $this->MODULE_ID,
            '\Shmt\Delivery\Build',
            'classNamesBuildList'
        );
    }
}
