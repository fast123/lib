<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Shmt\Webservice\Simple;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

/**
 * Class shmt_template
 *
 * название класса ложно совподать с дерикторией вместо . заменить _
 */
class shmt_webservice extends CModule
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
        
        $this->MODULE_ID = 'shmt.webservice';
        $this->MODULE_NAME = Loc::getMessage('SHMT_WEBSERVICE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SHMT_WEBSERVICE_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('SHMT_WEBSERVICE_MODULE_PARTNER_NAME');
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
        $this->installFiles();
        $this->installEvent();
    }

    /**
     * Удалениея модуля
     * запускается при нажатии кнопки Удалить на странице Модули административного раздела, осуществляет деинсталляцию модуля.
     */
    public function doUninstall()
    {
        $this->unInstallFiles();
        $this->unInstallEvent();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     *
     * Установка файлов в частности в админке
     *
     * @return bool
     */
    public function installFiles()
    {
        if (is_dir($p = str_replace('\\','/',dirname(__DIR__)).'/admin')) {
            if ($dir = opendir($p))
            {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || $item == 'menu.php')
                        continue;
                    $p = str_replace($_SERVER["DOCUMENT_ROOT"],'$_SERVER["DOCUMENT_ROOT"]."',$p);
                    file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item,
                        "<? require({$p}/{$item}\");?>");
                }
                closedir($dir);
            }
        }

        return true;
    }

    /**
     * Удаление установленных файлов админке
     * @return bool
     */
    public function unInstallFiles()
    {
        if (is_dir($p = str_replace('\\','/',dirname(__DIR__)).'/admin')) {
            if ($dir = opendir($p))	{
                while (false !== $item = readdir($dir))	{
                    if ($item == '..' || $item == '.') continue;
                    unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item);
                }
                closedir($dir);
            }
        }

        return true;
    }

    public function installEvent(){
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            '\Shmt\Webservice\Simple',
            'OnRestServiceBuildDescription'
        );
    }


    public function unInstallEvent(){
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            '\Shmt\Webservice\Simple',
            'OnRestServiceBuildDescription'
        );
    }
}
