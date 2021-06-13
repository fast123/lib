<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

class fourpx_simple_seo extends CModule
{
	public $MODULE_ID;
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_GROUP_RIGHTS;
	public $PARTNER_NAME;
	public $PARTNER_URI;

	public function __construct()
	{
		$this->MODULE_ID = "fourpx.simple.seo";
		$this->MODULE_VERSION = '0.0.1';
		$this->MODULE_VERSION_DATE = '2018-10-24 11:09:36';
		$this->MODULE_NAME = "SEO модуль";
		$this->MODULE_DESCRIPTION = "SEO модуль";
		$this->MODULE_GROUP_RIGHTS = 'Y';
		$this->PARTNER_NAME = "<div align='center'>- 4 PX -</div>";
		$this->PARTNER_URI = "https://4px.ru/";
	}
	
	public function InstallDB()
	{
		global $DB;
		$DB->RunSQLBatch(dirname(__FILE__)."/db/install.sql");

		return true;
	}

	public function UnInstallDB()
	{
		global $DB;
		$DB->RunSQLBatch(dirname(__FILE__)."/db/uninstall.sql");

		return true;
	}
	
    public function InstallEvents(){

        EventManager::getInstance()->registerEventHandler(
            "main",
            "OnEpilog",
            $this->MODULE_ID,
            "\FourPx\SimpleSEO\Processor",
            "setMeta"
        );

        return false;
    }

    public function UnInstallEvents(){

        EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnEpilog",
            $this->MODULE_ID,
            "\FourPx\SimpleSEO\Processor",
            "setMeta"
        );

        return false;
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


        # Не затираем компоненты при переустановке модуля >
        if (is_dir($p = str_replace('\\','/',dirname(__DIR__)).'/install/components')) {

            $componentsDirFrom = $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/components";
            $componentsDirTo = $_SERVER["DOCUMENT_ROOT"]."/local/components";

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
        # Не затираем компоненты при переустановке модуля <

        return true;
    }

    public function UnInstallFiles()
    {
        $admin_dir = str_replace("\\","/",__DIR__."/admin");

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
        $this->InstallDB();
        $this->InstallFiles();

        RegisterModule($this->MODULE_ID);

        $this->InstallEvents();
        #LocalRedirect("/bitrix/admin/settings.php?lang=ru&mid=fourpx.simple.seo&mid_menu=1");
    }

	public function doUninstall()
	{
		if ($_REQUEST['step'] <> 2)
		{
			$this->ShowDataSaveForm();
		}
		else
		{
			if ($_REQUEST["savedata"] != "Y") $this->UnInstallDB();
            
			$this->UnInstallFiles();
            $this->UnInstallEvents();

			UnRegisterModule($this->MODULE_ID);
		}
	}
	
	function ShowDataSaveForm()
	{
		global $APPLICATION, $USER, $adminPage, $adminMenu, $adminChain;
		$GLOBALS['APPLICATION']->SetTitle($this->MODULE_NAME);
		include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
		?>
		<!--form action="<?= $GLOBALS['APPLICATION']->GetCurPage()?>" method="get"-->
		<form action="<?= $GLOBALS['APPLICATION']->GetCurPage()?>" method="get">
			<input type="hidden" name="lang" value="<?=LANG?>" />
			<input type="hidden" name="id" value="<?= $this->MODULE_ID?>" />
			<input type="hidden" name="uninstall" value="Y" />
			<input type="hidden" name="step" value="2" />
			<input type="hidden" name="sessid" value="<?=$_REQUEST['sessid']?>" />
			<?CAdminMessage::ShowMessage("Внимание!<br />Модуль будет удален из системы")?>
			<p>Вы можете сохранить данные в таблицах базы данных:</p>
			<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked="checked" /><label for="savedata">Сохранить таблицы</label><br /></p>
			<input type="submit" name="inst" value="Удалить модуль" />
		</form>
		<?
		include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
		die();
	}
}
