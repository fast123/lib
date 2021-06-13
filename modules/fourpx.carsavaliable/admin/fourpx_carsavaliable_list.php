<?
define('ADMIN_MODULE_NAME', 'fourpx.carsavaliable');
define('LANG_CONST_PREFIX', 'FOURPX_CARFEED_TSK_');

use \Bitrix\Main\Loader,
    \Bitrix\Main\Config\Option,
    \FourPx\CarsAvaliable\Import,
    \FourPx\CarsAvaliable\LogTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\CJSCore::Init(array('jquery'));

if (! Loader::includeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage(LANG_CONST_PREFIX . "ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight(ADMIN_MODULE_NAME);
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage(LANG_CONST_PREFIX . "ACCESS_DENIED"));


if ($_REQUEST['ajax_mode'] == 'Y')
{
    if ($_REQUEST['action'] == 'import')
    {
        $import = new Import();
        echo $import->uploadToIBlock();
        die();
    }
}

$sTableID = LogTable::getTableName();
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

// ******************************************************************** //
//                ��������� �������� ��� ���������� ������              //
// ******************************************************************** //

if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT == 'W')
{
    if($_REQUEST['action_target']=='selected')
    {
        $arID[] = array();
        $rsExistsCars = LogTable::getList(array('select' => array('*')));
        while($arRes = $rsExistsCars->fetch())
            $arID[] = $arRes['Id'];
    }

    foreach($arID as $ID)
    {
        if(strlen($ID) <= 0)
            continue;

        $ID = IntVal($ID);

        switch($_REQUEST['action'])
        {
            case "delete":
                $res = LogTable::delete($ID);
                if (!$res->isSuccess()) {
                    $lAdmin->AddGroupError(GetMessage(LANG_CONST_PREFIX . "del_err"), $ID);
                }
                break;
        }
    }
}

// ******************************************************************** //
//                ������� ��������� ������                              //
// ******************************************************************** //

$rsData = array();
$rsExistsCars = LogTable::getList(array('select' => array('*'), 'order' => array('Id' => 'desc')));
$rsData = new CAdminResult($rsExistsCars, $sTableID);
$rsData->NavStart(20);
$lAdmin->NavText($rsData->GetNavPrint(GetMessage(LANG_CONST_PREFIX . 'NAV_TITLE')));

// ******************************************************************** //
//                ���������� ������ � ������                            //
// ******************************************************************** //

$arHeaders = array(
    array(
        'id' => 'Id',
        'sort' => 'Id',
        'content' => 'ID',
        "default"  =>true,
    ),
    array('id' => 'date_create',
        'sort' => 'date_create',
        'content' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_DATE_CREATE'),
        "default"  => true,
    ),
    array('id' => 'CountAll',
        'sort' => 'CountAll',
        'content' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_COUNT_ALL'),
        "default"  =>true,
    ),
    array('id' => 'CountUpdate',
        'sort' => 'CountUpdate',
        'content' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_COUNT_UPDATE'),
        "default"  =>true,
    ),
    array('id' => 'CountAdd',
        'sort' => 'CountAdd',
        'content' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_COUNT_ADD'),
        "default"  =>true,
    ),
    array('id' => 'CountDel',
        'sort' => 'CountDel',
        'content' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_COUNT_DEL'),
        "default"  =>true,
    ),
    array('id' => 'comment',
        'sort' => 'comment',
        'content' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_COMMENT'),
        "default"  =>true,
    ),
);

$lAdmin->AddHeaders($arHeaders);

while ($arRow = $rsData->GetNext()) {
    $itemId = $arRow['Id'];
    $row =& $lAdmin->AddRow($itemId, $arRow);
}

$lAdmin->AddFooter(
    array(
        array('title' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_SELECTED'), 'value' => $rsData->SelectedRowsCount()),
        array('counter' => true, 'title' => GetMessage(LANG_CONST_PREFIX . 'MAIN_ADMIN_LIST_CHECKED'), 'value' => '0'),
    )
);

$lAdmin->AddGroupActionTable(Array('delete' => true));

// ******************************************************************** //
//                ���������������� ����                                 //
// ******************************************************************** //


$aContext = array(
    array(
        'TEXT' => GetMessage(LANG_CONST_PREFIX . 'POST_ADD'),
        'LINK' => 'javascript:actionImport()',
        'TITLE' => GetMessage(LANG_CONST_PREFIX . 'POST_ADD_TITLE'),
        'ICON' => 'btn_new',
    ),
);
?>
<script>
    function actionImport()
    {
        $('.result_import_msg').show();
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: '/bitrix/admin/fourpx_carsavaliable_list.php?ajax_mode=Y&action=import',
            success: function (data) {
                console.log(data);
                $('.result_import_msg').hide();
                $('.result_import').html(data.HTML);
            },
        });
    }
</script>
<?
$lAdmin->AddAdminContextMenu($aContext);

// ******************************************************************** //
//                �����                                                 //
// ******************************************************************** //

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage(LANG_CONST_PREFIX . "TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$optionsModule = Option::getForModule(ADMIN_MODULE_NAME);
?>

<?if(!isset($optionsModule["iblock"]) or $optionsModule["iblock"] === "false"):?>
    <?CAdminMessage::ShowMessage(
        array(
            "MESSAGE"=> GetMessage(LANG_CONST_PREFIX . 'NEED_SET_MODULE'),
            "DETAILS"=> GetMessage(LANG_CONST_PREFIX . 'NEED_SET_MODULE_DETAIL'),
            "HTML"=>true,
        )
    );?>
<?else:?>
<div class="result_import_msg" style="display: none">
    <?CAdminMessage::ShowMessage(
        array(
            "MESSAGE"=> GetMessage(LANG_CONST_PREFIX . 'IMPORT_START'),
            "DETAILS"=> GetMessage(LANG_CONST_PREFIX . 'PLEASE_WAIT'),
            "HTML"=>true,
            "TYPE"=>"OK",
        )
    );?>
</div>
<div class="result_import"></div>
<?$lAdmin->DisplayList();?>
<?endif;?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');?>