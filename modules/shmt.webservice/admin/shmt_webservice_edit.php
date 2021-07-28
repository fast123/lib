<?php

use Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

Loc::loadMessages(__FILE__);

$moduleId = 'shmt.webservice';
$userRights = $APPLICATION->GetUserRight($moduleId);
$request = Application::getInstance()->getContext()->getRequest();
\CJSCore::Init(array('jquery'));

if ($userRights <= 'D') die('Доступ к модулю запрещён.');

$arParams = [
  'AP_EDIT_URL_TPL'=>'/bitrix/admin/shmt_webservice.php',
  'LIST_URL'=>'/bitrix/admin/shmt_webservice.php',
  'ID'=>$_REQUEST['id'],
];

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';?>

<div class="web-servisce">
    <?
    #TODO сделать свой шаблон компонента при установке модуля  + там уже подогнать контент и верстку
    $APPLICATION->IncludeComponent(
        'bitrix:rest.hook.ap.edit',
        '',
        array(
            'EDIT_URL_TPL' => $arParams['AP_EDIT_URL_TPL'],
            'LIST_URL' => $arParams['LIST_URL'],
            'ID' => $arParams['ID'],
            'SET_TITLE' => 'Y',
        ),
        $component
    );
    ?>
</div>

<?require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
