<?php

use Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc;

define('LANG_CONST_PREFIX', 'SHMT_TSK_');

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

Loc::loadMessages(__FILE__);

$moduleId = 'shmt.webservice';
$userRights = $APPLICATION->GetUserRight($moduleId);
$request = Application::getInstance()->getContext()->getRequest();
$urlEdit = '/bitrix/admin/shmt_webservice_edit.php';
\CJSCore::Init(array('jquery'));


if ($userRights <= 'D') die('Доступ к модулю запрещён.');

// Формируем таблицу
$sTableID = 'rest_hook_ap';//роизвольное название
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);
$arSelect = array('ID', 'DATE_CREATE', 'DATE_LOGIN', 'LAST_IP', 'TITLE', 'PASSWORD');

//выполняестя при групвых дейчвтиях над элементами списка в данном случае только удаление
//пример всех групповых действий https://dev.1c-bitrix.ru/api_help/main/general/admin.section/classes/cadminlist/groupaction.php
if(($arID = $lAdmin->GroupAction()))
{
    if($_REQUEST['action_target']=='selected')
    {
        $arID[] = array();
        $rsData = \Bitrix\Rest\APAuth\PasswordTable::getList(array(
            'select' => $arSelect
        ));
        while($arRes = $rsData->fetch())
            $arID[] = $arRes['ID'];
    }

    foreach($arID as $ID)
    {
        if(strlen($ID) <= 0)
            continue;

        $ID = IntVal($ID);

        switch($_REQUEST['action'])
        {
            case "delete":
                $res = Bitrix\Rest\APAuth\PasswordTable::delete($ID);
                if (!$res->isSuccess()) {
                    $lAdmin->AddGroupError(GetMessage(LANG_CONST_PREFIX . "del_err"), $ID);
                }
                break;
        }
    }
}

//получаем данные для таблицы
$rsData = \Bitrix\Rest\APAuth\PasswordTable::getList(array(
    'order' => array('ID' => 'DESC'),
    'select' => $arSelect
));
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(20);
$lAdmin->NavText($rsData->GetNavPrint(Loc::GetMessage(LANG_CONST_PREFIX . 'NAV_TITLE')));


//устанавливаем заголовки столбцов
foreach ($arSelect as $val){
    $arHeaders[] = [
        'id' => $val,
        'sort' => $val,
        'content' => $val,
        "default"  =>true,
    ];
}
$lAdmin->AddHeaders($arHeaders);

//добавляем строки
while ($arRow = $rsData->GetNext()) {
    $test = '';
    $row =& $lAdmin->AddRow($arRow['ID'], $arRow, $urlEdit.'?id='.$arRow['ID']);
}

//футер с кнопками
/*$lAdmin->AddFooter(
    array(
        array('title' => Loc::GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_SELECTED'), 'value' => $rsData->SelectedRowsCount()),
        array('counter' => true, 'title' => Loc::GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_CHECKED'), 'value' => '0'),
    )
);*/

$lAdmin->AddGroupActionTable(Array('delete' => true));

$aContext = array(
    array(
        'TEXT' => Loc::GetMessage(LANG_CONST_PREFIX . 'POST_ADD'),
        'LINK' => $urlEdit,//здесь можно указать js обработчик например 'javascript:actionImport()'
        'TITLE' => Loc::GetMessage(LANG_CONST_PREFIX . 'POST_ADD'),
        'ICON' => 'btn_new',
    ),
);?>
<?
//меню над листом с добавленой своей кнопкой $arContenxt
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';?>

<div class="web-servisce">
    <?$lAdmin->DisplayList();?>
</div>

<?require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
