<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Application;

$moduleId = 'fourpx.simple.seo';
$sTableID = 'fpx_simpleseo';

$userRights = $APPLICATION->GetUserRight($moduleId);

if ($userRights <= "D") die("Доступ к модулю запрещён.");

$request = Application::getInstance()->getContext()->getRequest();

\Bitrix\Main\Loader::IncludeModule($moduleId);


$recordId = $request->get('ID');
$backURL = '/bitrix/admin/fourpx_simple_seo_list.php';

if ($userRights == 'W') {

    if ($request->getPost('save') || $request->getPost('apply') || $request->getPost('save_and_new')) {

        $DB->PrepareFields($sTableID);

        $techUrl = '/' . trim(str_replace(['http://' . $_SERVER['SERVER_NAME'], 'https://' . $_SERVER['SERVER_NAME'], ],
                                    ['', ''],
                                    $request->getPost("URL")), '/') . '/';
        $techUrl = str_replace(['//', '.php/', '.html/'], ['/', '.php', '.html'], $techUrl);

        #$disclaimer = strip_tags($request->getPost("DISCLAIMER"), '<a><b><i><u><p><br><br/><strong><h2><h3><h4><em><ul><li><ol>');
        #$seoText = strip_tags($request->getPost("SEO_TEXT"), '<a><b><i><u><p><br><br/><strong><h2><h3><h4><em><ul><li><ol>');
        $disclaimer = $request->getPost("DISCLAIMER");
        $seoText = $request->getPost("SEO_TEXT");
        $seoText2 = $request->getPost("SEO_TEXT_2");


        $arFields = [
            "is_active" => "'" . ($request->getPost("ACTIVE") ? "Y" : "N") . "'",
            "url" => "'" . $DB->ForSql($request->getPost("URL")) . "'",
            "url_tech" => "'" . $DB->ForSql($techUrl) . "'",
            "seo_text" => "'" . $DB->ForSql($seoText) . "'",
            "seo_text_2" => "'" . $DB->ForSql($seoText2) . "'",
            "disclaimer" => "'" . $DB->ForSql($disclaimer) . "'",
            "title" => "'" . $DB->ForSql($request->getPost("TITLE")) . "'",
            "h1" => "'" . $DB->ForSql($request->getPost("H1")) . "'",
            "description" => "'" . $DB->ForSql($request->getPost("DESCRIPTION")) . "'",
            "keywords" => "'" . $DB->ForSql($request->getPost("KEY_WORDS")) . "'",
            "link_rel_canonical" => "'" . $DB->ForSql($request->getPost("REL_CANONICAL")) . "'",
            "link_rel_alternative" => "'" . $DB->ForSql($request->getPost("REL_ALTERNATIVE")) . "'",
            "sort" => "'" . $DB->ForSql($request->getPost("SORT")) . "'",
        ];

        if ($recordId > 0) {
            # изменение существующей записи

            $arFields['date_update'] = $DB->GetNowFunction();
            $arFields['updated_by'] = $USER->GetID();

            $res = $DB->Update($sTableID, $arFields, "WHERE ID='" . $ID . "'", $err_mess . __LINE__);
            var_dump($res);

            $status["MESSAGE"] = 'Изменения успешно сохранены';
        } else {
            # добавление записи

            $arFields['date_create'] = $DB->GetNowFunction();
            $arFields['created_by'] = $USER->GetID();

            $recordId = $DB->Insert($sTableID, $arFields, $err_mess . __LINE__);

            $status["MESSAGE"] = 'Данные успешно добавлены';
        }

        if ($request->getPost('save')) {
            LocalRedirect($backURL);
        }

        if ($request->getPost('save_and_new')) {
            LocalRedirect('/bitrix/admin/fourpx_simple_seo_detail.php?lang=ru');
        }

        if ($request->getPost('apply')) {
            LocalRedirect('/bitrix/admin/fourpx_simple_seo_detail.php?lang=ru&ID=' . $recordId);
        }
    }
}


if ($recordId){
    $aTabs = [
        [
            "DIV" => "answer",
            "TAB" => "Редактирование SEO-записи",
            "ICON" => "main_user_edit",
            "TITLE" => "Редактирование SEO-записи"
        ],
    ];
} else {
    $aTabs = [
        [
            "DIV" => "answer",
            "TAB" => "Добавление новой SEO-записи",
            "ICON" => "main_user_edit",
            "TITLE" => "Добавление новой SEO-записи"
        ],
    ];


    # Добавление активности и получение значения сортировки по умолчанию
    $arRecord['ACTIVE'] = 'Y';
    $arRecord['SORT'] = '100';

    if ($rsGetLastSort = $DB->Query("
        SELECT
            `sort` `SORT`
        FROM `" . $sTableID . "`
        WHERE `id` = (SELECT MAX(`id`) FROM `" . $sTableID . "`)
        ")
    ) {
        if ($lastSort = $rsGetLastSort->Fetch()) {
            $arRecord['SORT'] = $lastSort['SORT'] + 100;
        }
    }

}

if ($recordId > 0) {
    $arRecord = $DB->Query("
        SELECT
            `id` `ID`,
            `is_active` `ACTIVE`,
            `url` `URL`,
            `url_tech` `URL_TECH`,
            `seo_text` `SEO_TEXT`,
            `seo_text_2` `SEO_TEXT_2`,
            `disclaimer` `DISCLAIMER`,
            `title` `TITLE`,
            `h1` `H1`,
            `description` `DESCRIPTION`,
            `keywords` `KEY_WORDS`,
            `link_rel_canonical` `REL_CANONICAL`,
            `link_rel_alternative` `REL_ALTERNATIVE`,
            `sort` `SORT`
        FROM `" . $sTableID . "` WHERE `id` = '" . $recordId . "';"
    )->Fetch();
}


$tabControl = new CAdminTabControl("tabs", $aTabs, false);

$APPLICATION->SetTitle(($recordId > 0 ? "Редактирование SEO-записи" : "Добавление новой SEO-записи"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


$aMenu = [
    [
        'TEXT' => 'Вернуться к списку SEO-записей',
        'TITLE' => 'Вернуться к списку SEO-записей',
        'LINK' => $backURL,
        'ICON' => 'btn_list',
    ],
];

$context = new CAdminContextMenu($aMenu);

$context->Show();
?>

<? $tabControl->Begin() ?>


<?
    if (! empty($status)) {
        CAdminMessage::ShowMessage(["MESSAGE" => $status["MESSAGE"], "TYPE" => $status["ID"]]);
    }
?>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">

    <?$tabControl->BeginNextTab()?>

    <tr>
        <td width='40%'>Активность:</td>
        <td class='adm-detail-content-cell-r'>
            <input type='checkbox' name='ACTIVE' value="<?= $arRecord['ACTIVE'] == 'Y' ? 'Y' : 'N'?>" id='activeCheckBox' <?= $arRecord['ACTIVE'] == 'Y' ? 'checked="checked"' : ''?> class='adm-designed-checkbox'>
            <label class='adm-designed-checkbox-label' for='activeCheckBox' title=''></label>
        </td>
    </tr>
    <tr>
        <td><b>Адрес страницы (URL):</b></td>
        <td><input type='text' name='URL' value='<?= $arRecord['URL'] ? htmlspecialchars($arRecord['URL']) : ''?>' maxlength='1000' style='width: 100%'></td>
    </tr>
    <tr>
        <td>Title:</td>
        <td><input type='text' name='TITLE' value='<?= $arRecord['TITLE'] ? htmlspecialchars($arRecord['TITLE']) : ''?>' maxlength='255' style='width: 100%'></td>
    </tr>
    <tr>
        <td>H1:</td>
        <td><input type='text' name='H1' value='<?= $arRecord['H1'] ? htmlspecialchars($arRecord['H1']) : ''?>' maxlength='255' style='width: 100%'></td>
    </tr>
    <tr>
        <td>Description:</td>
        <td>
            <textarea name='DESCRIPTION' style='text-align: left; width: 100%; height: 100px; resize: none;'><?= $arRecord['DESCRIPTION'] ? htmlspecialchars($arRecord['DESCRIPTION']) : ''?></textarea>
    </tr>
    <tr>
        <td>Key words:</td>
        <td><input type='text' name='KEY_WORDS' value='<?= $arRecord['KEY_WORDS'] ? htmlspecialchars($arRecord['KEY_WORDS']) : ''?>' maxlength='255' style='width: 100%'></td>
    </tr>
    <tr>
        <td>Link Canonical:</td>
        <td><input type='text' name='REL_CANONICAL' value='<?= $arRecord['REL_CANONICAL'] ? htmlspecialchars($arRecord['REL_CANONICAL']) : ''?>' maxlength='500' style='width: 100%'></td>
    </tr>
    <tr>
        <td>Link Alternative:</td>
        <td><input type='text' name='REL_ALTERNATIVE' value='<?= $arRecord['REL_ALTERNATIVE'] ? htmlspecialchars($arRecord['REL_ALTERNATIVE']) : ''?>' maxlength='500' style='width: 100%'></td>
    </tr>
    <tr>
        <td>Сортировка:</td>
        <td><input type='text' name='SORT' value='<?= $arRecord['SORT'] ? htmlspecialchars($arRecord['SORT']) : ''?>' maxlength='6'></td>
    </tr>
    <tr>
        <td colspan="2">
            <p align="left">SEO-текст (html):</p>
            <textarea name='SEO_TEXT' style='text-align: left; width: 100%; height: 200px; resize: vertical;'><?= $arRecord['SEO_TEXT'] ? htmlspecialchars($arRecord['SEO_TEXT']) : ''?></textarea>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <p align="left">SEO-текст 2 (html):</p>
            <textarea name='SEO_TEXT_2' style='text-align: left; width: 100%; height: 200px; resize: vertical;'><?= $arRecord['SEO_TEXT_2'] ? htmlspecialchars($arRecord['SEO_TEXT_2']) : ''?></textarea>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <p align="left">Дисклеймер (html):</p>
            <textarea name='DISCLAIMER' style='text-align: left; width: 100%; height: 200px; resize: vertical;'><?= $arRecord['DISCLAIMER'] ? htmlspecialchars($arRecord['DISCLAIMER']) : ''?></textarea>
        </td>
    </tr>

    <?= bitrix_sessid_post()?>

    <input type='hidden' name='ID' value='<?= $recordId?>'>

    <?
    $tabControl->Buttons([
            'disabled' => $userRights == 'W' ? false : true,
            'back_url' => $backURL,
        ]
    );
    ?>
    <input class="adm-btn-green" style="float: left" type="submit" name="save_and_new" value="Сохранить и Создать" title="Сохранить и создать новую запись" />

    <? $tabControl->End()?>

</form>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
