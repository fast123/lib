<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Application,
    \Fourpx\CarsAvaliable\LogTable,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Entity\Base,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class fourpx_carsavaliable extends CModule
{
	public $MODULE_ID;
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_GROUP_RIGHTS;
	public $PARTNER_NAME;
	public $PARTNER_URI;
	public $ENTITY_NAME;

	public function __construct()
	{
	    $arModuleVersion = array();
	    include (__DIR__ . '/version.php');

		$this->MODULE_ID = 'fourpx.carsavaliable';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');

		$this->PARTNER_NAME = Loc::getMessage('PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('PARTNER_URL');

		$this->MODULE_SORT = 1;
		$this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->ENTITY_NAME = '\Fourpx\CarsAvaliable\LogTable';

        Loader::includeModule($this->MODULE_ID);
	}

	public function InstallDB()
	{
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(LogTable::getConnectionName())->isTableExists(
            Base::getInstance($this->ENTITY_NAME)->getDBTableName()
        )
        ) {
            Base::getInstance($this->ENTITY_NAME)->createDbTable();
        }
	}

	public function UnInstallDB()
	{
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(LogTable::getConnectionName())
            ->queryExecute('drop table if exists ' . Base::getInstance($this->ENTITY_NAME)->getDBTableName());
	}

    public function InstallEvents()
	{
	    return true;
    }

    public function UnInstallEvents()
	{
        return true;
    }

    public function InstallFiles()
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


        # �� �������� ���������� ��� ������������� ������ >
        if (is_dir($p = str_replace('\\','/',dirname(__DIR__)).'/install/components')) {

            $componentsDirFrom = $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/components";
            $componentsDirTo = $_SERVER["DOCUMENT_ROOT"]."/local/templates/.default/components";

            $componentsNamespaceList = glob($componentsDirFrom."/*", GLOB_ONLYDIR);

            foreach ($componentsNamespaceList as $namespace) {
                $namespace = end(explode("/", $namespace));

                $componentsList = glob($componentsDirFrom."/".$namespace."/*", GLOB_ONLYDIR);

                foreach ($componentsList as $componentName) {
                    $componentName = end(explode("/", $componentName));

                    if (is_dir( $componentsDirTo."/".$namespace."/".$componentName )) {
                        rename( $componentsDirTo."/".$namespace."/".$componentName, $componentsDirTo."/".$namespace."/".$componentName."_deleted_".date("Y_m_d_H_i_s"));
                    }
                }
            }

            CopyDirFiles($componentsDirFrom, $componentsDirTo, false, true);
        }
        # �� �������� ���������� ��� ������������� ������ <

        return true;
    }

    public function UnInstallFiles()
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

    public function doInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            \CAgent::AddAgent( "\\Progect\\CarsAvaliable\\Import::startImport();", $this->MODULE_ID, "N", 3600);

            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();

        } else {
            $APPLICATION->ThrowException(Loc::getMessage('INSTALL_ERROR_VERSION'));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage('INSTALL_TITLE'), $this->GetPath() . '/install/step.php');
    }

	public function doUninstall()
	{
	    global $APPLICATION, $_REQUEST;

	    $request = Application::getInstance()->getContext()->getRequest();
	    
		if ($request['step'] < 2)
		{
            $APPLICATION->IncludeAdminFile(Loc::getMessage('UNINSTALL_TITLE'), $this->GetPath() . '/install/unstep1.php');
		}
		elseif ($request['step'] == 2)
        {
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->UnInstallDB();

            \CAgent::RemoveModuleAgents($this->MODULE_ID);

            Option::delete($this->MODULE_ID);

            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(Loc::getMessage('UNINSTALL_TITLE'), $this->GetPath() . '/install/unstep2.php');
		}
	}


    private static function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    private static function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    public static function getModulePath()
    {
        return false;
    }
}
