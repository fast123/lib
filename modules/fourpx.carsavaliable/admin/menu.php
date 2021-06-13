<?php defined("B_PROLOG_INCLUDED") and (B_PROLOG_INCLUDED === true) or die();

$moduleId = "fourpx.carsavaliable";

if ($APPLICATION->GetUserRight($moduleId) <= "D") return false;

$CSSPath = substr(__DIR__, strlen($_SERVER["DOCUMENT_ROOT"])) . DIRECTORY_SEPARATOR . "..".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."style.css";
$CSSPath = str_replace("\\", "/", $CSSPath);
$APPLICATION->SetAdditionalCSS($CSSPath);


$aMenu = array(
    array(
		'parent_menu' => 'global_menu_services',
		'section' => 'for_manager',
		'sort' => 6400,
		'text' => GetMessage("FOURPX_CARFEED_TSK_ADMIN_MENU_TITLE"),
		'title' => GetMessage("FOURPX_CARFEED_TSK_ADMIN_MENU_TITLE"),
		'url' => 'fourpx_carsavaliable_list.php',
		'more_url' => array(),
		'icon'	=>	'fourpx_carsavaliable_icon',
		'page_icon'	=>	'',
		'module_id' => $moduleId,
		'items_id' => 'menu_references',
    ),
);

return $aMenu;