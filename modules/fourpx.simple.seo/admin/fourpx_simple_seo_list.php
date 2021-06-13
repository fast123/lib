<?php require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Application;

$moduleId = 'fourpx.simple.seo';

$userRights = $APPLICATION->GetUserRight($moduleId);

if ($userRights <= 'D') die('Доступ к модулю запрещён.');

$request = Application::getInstance()->getContext()->getRequest();

$arFilterSettings = $request->getQuery('FILTER');

\Bitrix\Main\Loader::IncludeModule($moduleId);

$sTableID = 'fpx_simpleseo';
$navTitle = 'SEO-записи';
$editURL = 'fourpx_simple_seo_detail.php?lang=' . LANG;

# SORT
$sortBy = !empty($_REQUEST[$sTableID."by"]) ? $_REQUEST[$sTableID."by"] : "seo.ID";
$sortOrder = !empty($_REQUEST[$sTableID."sort"]) ? $_REQUEST[$sTableID."sort"] : "desc";


$oSort = new CAdminSorting($sTableID, $sortBy, $sortOrder, $sTableID."by", $sTableID."sort");
$lAdmin = new CAdminList($sTableID, $oSort);

# WHERE
function addFilterToQuery()
{
	global $DB, $refOrderStatus, $refOrderStatusInitiator;

	$query = "";
	if ($_REQUEST["set_filter"] == "Y")
	{
		$arQuery = [];

        foreach ($_REQUEST['FILTER'] as $filterKey => $filterValue) {

            $filterKey = $DB->ForSQL(trim($filterKey));
            $filterValue = $DB->ForSQL(trim($filterValue));

            if ($filterKey && $filterValue) {
                $arQuery[] = $filterKey . " like '" . $filterValue . "'";
            }
		}

		$query = implode(' AND ', $arQuery);
	}

	if (! empty($query)) {
	    $query = ' WHERE ' . $query;
    }

	return $query . ' ';
}

$query = '
        SELECT
            `seo`.`id` `ID`,
            `seo`.`is_active` `ACTIVE`,
            `seo`.`url` `URL`,
            `seo`.`title` `TITLE`,
            `seo`.`h1` `H1`,
            `seo`.`description` `DESCRIPTION`,
            `seo`.`keywords` `KEY_WORDS`,
            `seo`.`link_rel_canonical` `REL_CANONICAL`,
            `seo`.`link_rel_alternative` `REL_ALTERNATIVE`,
            `seo`.`sort` `SORT`,
            `seo`.`seo_text` `SEO_TEXT`,
            `seo`.`seo_text_2` `SEO_TEXT_2`,
            `seo`.`disclaimer` `DISCLAIMER`
        FROM `' . $sTableID . '` `seo`
	' . addFilterToQuery() . ' ORDER BY ' . $sortBy . ' ' . $sortOrder . ';';

$rsList = $DB->Query($query, false);

$rsList = new CAdminResult($rsList, $sTableID);

$rsList->NavStart();
$lAdmin->NavText($rsList->GetNavPrint($navTitle));

$lAdmin->AddHeaders([
		['id' => 'ID',		            'content' => 'ID',					            'sort' => 'seo.ID',				    'default' => true,],
		['id' => 'ACTIVE',		        'content' => 'Активность',				        'sort' => 'seo.ACTIVE',	    	    'default' => true,],
		['id' => 'URL',					'content' => 'Адрес страницы (URL)',	    	'sort' => 'seo.URL',				'default' => true,],
		['id' => 'TITLE',		        'content' => 'Title',		                   	'sort' => 'seo.TITLE',		        'default' => true,],
		['id' => 'H1',	            	'content' => 'H1',			                    'sort' => 'seo.H1',		            'default' => true,],
		['id' => 'DESCRIPTION',		    'content' => 'Description',	                	'sort' => 'seo.DESCRIPTION',		'default' => true,],
        ['id' => 'KEY_WORDS',           'content' => 'Key words',                       'sort' => 'seo.KEY_WORDS',          'default' => true,],
        ['id' => 'REL_CANONICAL',       'content' => 'Link Canonical',                  'sort' => 'seo.REL_CANONICAL',      'default' => true,],
		['id' => 'REL_ALTERNATIVE',		'content' => 'Link Alternative',	            'sort' => 'seo.REL_ALTERNATIVE',	'default' => true,],
		['id' => 'SORT',		        'content' => 'Сортировка',	                	'sort' => 'seo.SORT',	         	'default' => true,],
        ['id' => 'SEO_TEXT',            'content' => 'SEO-текст',				        'sort' => 'seo.SEO_TEXT',           'default' => true,],
        ['id' => 'SEO_TEXT_2',          'content' => 'SEO-текст 2',				        'sort' => 'seo.SEO_TEXT_2',         'default' => true,],
		['id' => 'DISCLAIMER',          'content' => 'Дисклеймер',				        'sort' => 'seo.DISCLAIMER',         'default' => true,],
	]);

while($arRes = $rsList->NavNext(true, "f_")) {
	$row =& $lAdmin->AddRow($arRes["ID"], $arRes);

    $row->AddViewField('ACTIVE', $arRes['ACTIVE'] == 'Y' ? 'да' : 'нет');

	if ($userRights == 'W') {
        $arActions = [];
        $arActions[] = [
            'ICON' => 'edit',
            'DEFAULT' => true,
            'TEXT' => 'Редактировать',
            'ACTION' => $lAdmin->ActionRedirect($editURL . '&ID=' . $arRes['ID'])
        ];

        if ($arRes['ACTIVE'] == 'Y') {
            $arActions[] = [
                'ICON' => 'deactivate',
                'DEFAULT' => false,
                'TEXT' => 'Деактивировать',
                'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'deactivate')
            ];
        } else {
            $arActions[] = [
                'ICON' => 'activate',
                'DEFAULT' => false,
                'TEXT' => 'Активировать',
                'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'activate')
            ];
        }

        if(is_set($arActions[count($arActions)-1], 'SEPARATOR'))
            unset($arActions[count($arActions)-1]);
        $row->AddActions($arActions);
    }
}

$aContext = [
    [
        "TEXT" => "Добавить SEO-запись",
        "LINK" => $editURL,
        "TITLE" => "Добавить SEO-запись",
        "ICON" => "btn_new",
    ],
];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();
$APPLICATION->SetTitle($navTitle);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>


<form name="find_form" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$arFindFields = ['Активность', 'Адрес страницы (URL)', 'Title', 'H1', 'Description', 'Key words', 'Link Canonical', 'Link Alternative'];
$oFilter = new CAdminFilter(
	"filter_".$sTableID,
	$arFindFields
);

$oFilter->Begin();
?>

<tr>
    <td>Активность:</td>
    <td class="adm-filter-item-center">
        <div class="adm-filter-alignment">
            <div class="adm-filter-box-sizing">
                <span class="adm-select-wrap">
                    <select name="FILTER[is_active]" class="adm-select">
                        <option value="">(любой)</option>
                        <option value="Y">Да</option>
                        <option value="N">Нет</option>
			        </select>
                </span>
            </div>
        </div>
    </td>
</tr>
<tr>
	<td>Адрес страницы (URL):</td>
	<td><input type="text" name="FILTER[url]" value="<?echo htmlspecialcharsbx($arFilterSettings['URL_REDIRECT_FROM'])?>" size="50"></td>
</tr>
<tr>
    <td>Title:</td>
    <td><input type="text" name="FILTER[title]" value="<?echo htmlspecialcharsbx($arFilterSettings['TITLE'])?>" size="50"></td>
</tr>
<tr>
    <td>H1:</td>
    <td><input type="text" name="FILTER[h1]" value="<?echo htmlspecialcharsbx($arFilterSettings['H1'])?>" size="50"></td>
</tr>
<tr>
    <td>Description:</td>
    <td><input type="text" name="FILTER[description]" value="<?echo htmlspecialcharsbx($arFilterSettings['DESCRIPTION'])?>" size="50"></td>
</tr>
<tr>
    <td>Key words:</td>
    <td><input type="text" name="FILTER[keywords]" value="<?echo htmlspecialcharsbx($arFilterSettings['KEY_WORDS'])?>" size="50"></td>
</tr>
<tr>
    <td>Key words:</td>
    <td><input type="text" name="FILTER[link_rel_canonical]" value="<?echo htmlspecialcharsbx($arFilterSettings['REL_CANONICAL'])?>" size="50"></td>
</tr>
<tr>
    <td>Key words:</td>
    <td><input type="text" name="FILTER[link_rel_alternative]" value="<?echo htmlspecialcharsbx($arFilterSettings['REL_ALTERNATIVE'])?>" size="50"></td>
</tr>

<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>





<?
$lAdmin->DisplayList();
?>

<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        Изменить настройки модуля вы можете в <a href="settings.php?lang=ru&mid_menu=1&mid=four.px.simple.seo">настройках модуля</a>.
    </div>
</div>

<?

CJSCore::Init(array('window', 'jquery'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
