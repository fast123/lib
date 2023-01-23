<?php

//документация https://dev.1c-bitrix.ru/api_help/main/general/admin.section/rubric_admin_ex.php

use Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    LibSite\RestApi\UsersTable;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('libsite.restapi');
$moduleId = 'libsite.restapi';
$POST_RIGHT = $APPLICATION->GetGroupRight($moduleId);
$urlEdit = '/bitrix/admin/libsite_restapi_user_edit.php';
\CJSCore::Init(['jquery']);

if ($POST_RIGHT <= 'D') die('Доступ к модулю запрещён.');

// Формируем таблицу
$sTableID = 'libsite_restapi_user';//роизвольное название
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);
$arSelect = ['ID', 'LOGIN', 'PASSWORD', 'ACCESS_AREA'];

//ОБРАБОТКА ДЕЙСТВИЙ НАД ЭЛЕМЕНТАМИ СПИСКА
// сохранение отредактированных элементов
if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
    // пройдем по списку переданных элементов
    foreach($FIELDS as $ID=>$arFields)
    {
        if(!$lAdmin->IsUpdated($ID))
            continue;

        // сохраним изменения каждого элемента
        $DB->StartTransaction();
        $ID = IntVal($ID);
        $cData = new CRubric;
        if(($rsData = $cData->GetByID($ID)) && ($arData = $rsData->Fetch()))
        {
            foreach($arFields as $key=>$value)
                $arData[$key]=$value;
            if(!$cData->Update($ID, $arData))
            {
                $lAdmin->AddGroupError(Loc::getMessage("SAVE_ERROR")." ".$cData->LAST_ERROR, $ID);
                $DB->Rollback();
            }
        }
        else
        {
            $lAdmin->AddGroupError(Loc::getMessage("SAVE_ERROR")." ".GetMessage("NO_FIND"), $ID);
            $DB->Rollback();
        }
        $DB->Commit();
    }
}

// обработка одиночных и групповых действий
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
    // если выбрано "Для всех элементов"
    if($_REQUEST['action_target']=='selected')
    {
        $cData = new CRubric;
        $rsData = UsersTable::getList([
            'order' => ['ID' => 'DESC'],
            'select' => $arSelect
        ]);
        while($arRes = $rsData->fetch())
            $arID[] = $arRes['ID'];
    }

    // пройдем по списку элементов
    foreach($arID as $ID)
    {
        if(strlen($ID)<=0)
            continue;
        $ID = IntVal($ID);

        // для каждого элемента совершим требуемое действие
        switch($_REQUEST['action'])
        {
            // удаление
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                $resultDelete = UsersTable::delete($ID);
                if(!$resultDelete->isSuccess())
                {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(Loc::getMessage('DELETE_ERROR'), $ID);
                }
                $DB->Commit();
                break;

            // активация/деактивация
            case "activate":
            case "deactivate":
                $lAdmin->AddGroupError('Не предусмотрено, удаляй!', $ID);
                break;
        }
    }
}

//получаем данные для таблицы
$rsData = UsersTable::getList(array(
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
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(LOc::getMessage('TITLE'));

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
?>
    <div class="libsite-restapi">
        <?$lAdmin->DisplayList();?>
    </div>
<?
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
