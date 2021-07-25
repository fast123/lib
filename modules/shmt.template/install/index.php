<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Shmt\Template\ExampleTable;

Loc::loadMessages(__FILE__);

/**
 * Class shmt_template
 *
 * название класса ложно совподать с дерикторией вместо . заменить _
 */
class shmt_template extends CModule
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
        
        $this->MODULE_ID = 'shmt.template';
        $this->MODULE_NAME = Loc::getMessage('MODULE_TEMPLATE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_TEMPLATE_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('MODULE_TEMPLATE_MODULE_PARTNER_NAME');
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
        $this->installDB();
        $this->installEvents();
        $this->installFiles();
    }

    /**
     * Удалениея модуля
     * запускается при нажатии кнопки Удалить на странице Модули административного раздела, осуществляет деинсталляцию модуля.
     */
    public function doUninstall()
    {
        $this->uninstallDB();
        $this->unInstallEvents();
        $this->unInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     *
     * Установка бд
     *
     * @return false|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            ExampleTable::getEntity()->createDbTable();
        }
    }

    /**
     *
     * удаление бд
     *
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\LoaderException
     */
    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            $tableName = ExampleTable::getTableName();
            $connection = Application::getInstance()->getConnection();
            if($connection->isTableExists($tableName)){
                $connection->dropTable($tableName);
            }
        }
    }

    /**
     * Установка агентов
     * @return bool
     */
    public function installEvents()
    {
        \CAgent::AddAgent( "\\Shmt\\Template\\Import::startImport();", $this->MODULE_ID, "N", 3600);
        return true;
    }

    /**
     * Удаление всех агентов модуля агентов
     * @return bool
     */
    public function unInstallEvents()
    {
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
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
}
