<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menu = array(
    array(
        'parent_menu' => 'global_menu_services',
        'sort' => 10000,
        'text' => Loc::getMessage('SHMT_WEBSERVICE_MENU_TITLE'),
        'title' => Loc::getMessage('SHMT_WEBSERVICE_MENU_TITLE'),
        'url' => 'shmt_webservice.php',
        'items_id' => 'menu_references',
    ),
);

return $menu;
