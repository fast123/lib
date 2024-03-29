<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use LibSite\RestApi\UsersTable;

Loc::loadMessages(__FILE__);

/**
 * Class libsite_restapi
 *
 */
class libsite_restapi extends CModule
{

    const CLASS_NAME_API = 'LibSiteRestApi';
    const MAIN_DIR_API = '/api/';
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'libsite.restapi';
        $this->MODULE_NAME = Loc::getMessage('MODULE_RESTAPI_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_RESTAPI_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('MODULE_RESTAPI_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = '';

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->mainDirApiDef = '/api/';
        $this->mainDirApi = $_SERVER['DOCUMENT_ROOT'].$this->mainDirApiDef;
        //$this->classDirApi = $this->mainDirApi.self::CLASS_NAME_API;
    }

    /**
     *  Установка модуля
     * запускается при нажатии кнопки Установить на странице Модули
     */
    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
        $this->installFiles();
    }

    /**
     * Удалениея модуля
     * запускается при нажатии кнопки Удалить на странице Модули административного раздела, осуществляет деинсталляцию модуля.
     */
    public function doUninstall()
    {
        $this->uninstallDB();
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
            UsersTable::getEntity()->createDbTable();
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
            $tableName = UsersTable::getTableName();
            $connection = Application::getInstance()->getConnection();
            if($connection->isTableExists($tableName)){
                $connection->dropTable($tableName);
            }
        }
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


        if(!is_dir($this->mainDirApi)) {
            mkdir($this->mainDirApi, 0777, true);
        }

        if (is_dir($p = str_replace('\\','/',dirname(__DIR__)).'/public_file')) {
            self::copyAll($p, $this->mainDirApi, false);
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

    private function removeDir($path) {
        if (is_file($path)) {
          @unlink($path);
        } else {
            array_map('removeDir',glob('/*')) == @rmdir($path);
        }
        @rmdir($path);
    }

    private function copyAll($from, $to, $rewrite = true) {
        if (is_dir($from)) {
            @mkdir($to);
            $d = dir($from);
            while (false !== ($entry = $d->read())) {
                if ($entry == "." || $entry == "..") continue;
                self::copyAll("$from/$entry", "$to/$entry", $rewrite);
            }
            $d->close();
        } else {
            if (!file_exists($to) || $rewrite)
                copy($from, $to);
        }
    }
}
