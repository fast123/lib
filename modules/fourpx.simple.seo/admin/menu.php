<?php defined("B_PROLOG_INCLUDED") and (B_PROLOG_INCLUDED === true) or die();

if ($APPLICATION->GetUserRight("fourpx.simpe.seo") <= "D") return false;

$CSSPath = substr(__DIR__, strlen($_SERVER["DOCUMENT_ROOT"])) . DIRECTORY_SEPARATOR . "..".DIRECTORY_SEPARATOR."admin_icons".DIRECTORY_SEPARATOR."style.css";
$CSSPath = str_replace("\\", "/", $CSSPath);

$APPLICATION->SetAdditionalCSS($CSSPath);

$module_id = "fourpx.simple.seo";

$aMenu = [
	[
		'parent_menu' => 'global_menu_services',
#		'parent_menu' => 'global_menu_marketing',
		'section' => 'for_manager',
		'sort' => 1200,
		'text' => 'SEO сайта',
		'title' => 'SEO сайта',
		'url' => 'fourpx_simple_seo_list.php',
		'more_url' => ['fourpx_simple_seo_detail.php'],
		'icon'	=>	'fourpx_simple_seo_icon',
		'page_icon'	=>	'',
		'module_id' => $module_id,
		'items_id' => 'menu_references',
	],
];

return $aMenu;