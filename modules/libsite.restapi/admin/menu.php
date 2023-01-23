<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menu = array(
    array(
        'parent_menu' => 'global_menu_services',
        'sort' => 10000,
        'text' => Loc::getMessage('MODULE_RESIAPI_MENU_TITLE'),
        'title' => Loc::getMessage('MODULE_RESIAPI_MENU_TITLE'),
        'url' => 'libsite_restapi_user.php',
        'items_id' => 'menu_references',
       /* 'items' => array(
            array(
                'text' => Loc::getMessage('MODULE_RESIAPI_SUBMENU_TITLE'),
                'url' => 'libsite_restapi_user.php?param1=paramval&lang=' . LANGUAGE_ID,
                'more_url' => array('libsite_restapi_user.php?param1=paramval&lang=' . LANGUAGE_ID),
                'title' => Loc::getMessage('MODULE_RESIAPI_SUBMENU_TITLE'),
            ),
        ),*/
    ),
);

return $menu;
