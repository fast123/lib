<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menu = array(
    array(
        'parent_menu' => 'global_menu_services',
        'sort' => 10000,
        'text' => Loc::getMessage('MODULE_TEMPLATE_MENU_TITLE'),
        'title' => Loc::getMessage('MODULE_TEMPLATE_MENU_TITLE'),
        'url' => 'shmt_template.php',
        'items_id' => 'menu_references',
        'items' => array(
            array(
                'text' => Loc::getMessage('MODULE_TEMPLATE_SUBMENU_TITLE'),
                'url' => 'shmt_template.php?param1=paramval&lang=' . LANGUAGE_ID,
                'more_url' => array('shmt_template.php?param1=paramval&lang=' . LANGUAGE_ID),
                'title' => Loc::getMessage('MODULE_TEMPLATE_SUBMENU_TITLE'),
            ),
        ),
    ),
);

return $menu;
