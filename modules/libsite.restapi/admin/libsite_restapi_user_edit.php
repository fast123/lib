<?php
//докумментация https://dev.1c-bitrix.ru/api_help/main/general/admin.section/rubric_edit_ex.php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    LibSite\RestApi\UsersTable;

Loc::loadMessages(__FILE__);

$moduleId = 'libsite.restapi';
if(!\Bitrix\Main\Loader::includeModule('libsite.restapi')){
    die($moduleId);
}

$POST_RIGHT = $APPLICATION->GetGroupRight($moduleId);
\CJSCore::Init(['jquery']);

if ($POST_RIGHT <= 'D') die('Доступ к модулю запрещён.');

// сформируем список закладок
$aTabs = [
    ["DIV" => "edit1", "TAB" => 'Доступы', "ICON"=>"main_user_edit", "TITLE"=>'Доступы'],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$ID = intval($_REQUEST['id']);// идентификатор редактируемой записи
if(empty($ID)){
    $ID = intval($_REQUEST['ID']);
}
$message = null;		// сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

//ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ
if(
    $REQUEST_METHOD == "POST" // проверка метода вызова страницы
    &&
    ($save!="" || $apply!="") // проверка нажатия кнопок "Сохранить" и "Применить"
    &&
    $POST_RIGHT=="W"          // проверка наличия прав на запись для модуля
    &&
    check_bitrix_sessid()     // проверка идентификатора сессии
)
{
    // обработка данных формы
    $arFields = [
        "LOGIN" => $LOGIN,
        "PASSWORD" => $PASSWORD,
        "ACCESS_AREA" => $ACCESS_AREA,
    ];

    // сохранение данных
    if($ID > 0)
    {
        $res = UsersTable::update($ID, $arFields);
    }
    else
    {
        $res = UsersTable::add($arFields);
        if($res->isSuccess()){
            $ID = $res->getId();
        }
    }

    if($res->isSuccess())
    {
        // если сохранение прошло удачно - перенаправим на новую страницу
        // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
        if ($apply != "")
            // если была нажата кнопка "Применить" - отправляем обратно на форму.
            LocalRedirect("/bitrix/admin/libsite_restapi_user_edit.php?id=".$ID."&mess=ok⟨=".LANG."&".$tabControl->ActiveTabParam());
        else
            // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
            LocalRedirect("/bitrix/admin/libsite_restapi_user.php?lang=".LANG);
    }
    else
    {
        // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if($error = $res->getErrorMessages()[0])
            $message = new CAdminMessage('Ошибка! '.$error);
        $bVarsFromForm = true;
    }
}


//ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ
// значения по умолчанию
$str_SORT          = 100;
$str_ACTIVE        = "Y";
$str_AUTO          = "N";
$str_DAYS_OF_MONTH = "";
$str_DAYS_OF_WEEK  = "";
$str_TIMES_OF_DAY  = "";
$str_VISIBLE       = "Y";
$str_LAST_EXECUTED = ConvertTimeStamp(false, "FULL");
$str_FROM_FIELD    = COption::GetOptionString("libsite.restapi", "default_from");

// выборка данных
if($ID>0)
{
    $resData = UsersTable::getList([
        'filter'=>['ID'=>$ID],
        'limit'=>1
    ]);
    while($tmp = $resData->fetch()){
        $arDataForm = $tmp;
    }

    if(empty($arDataForm)){
        die('Запись не найдена');
    }
}

// если данные переданы из формы, инициализируем их
if($bVarsFromForm)
    $DB->InitTableVarsForEdit("libsite_restapi_users", "", "str_");
?>
<?
$aMenu = [
    [
        "TEXT"  => Loc::getMessage("LIBSITE_RESTAPI_TITLE"),
        "TITLE" => Loc::getMessage("LIBSITE_RESTAPI_TITLE"),
        "LINK"  => "libsite_restapi_user.php?lang=".LANG,
        "ICON"  => "btn_list",
    ]
];

if($ID>0)
{
    $aMenu[] = ["SEPARATOR"=>"Y"];
    $aMenu[] = [
        "TEXT"  => Loc::getMessage("LIBSITE_RESTAPI_DELETE_BUTTON"),
        "TITLE" => Loc::getMessage("LIBSITE_RESTAPI_DELETE_BUTTON"),
        "LINK"  => "javascript:if(confirm('". Loc::getMessage("LIBSITE_RESTAPI_DELETE_CONFIRM") ."')) ".
            "window.location='libsite_restapi_user.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
        "ICON"  => "btn_delete",
    ];
    $aMenu[] = ["SEPARATOR"=>"Y"];
}
else{
    $aMenu[] = [
        "TEXT"  => Loc::getMessage("LIBSITE_RESTAPI_ADD_BUTTON"),
        "TITLE" => Loc::getMessage("LIBSITE_RESTAPI_ADD_BUTTON"),
        "LINK"  => "libsite_restapi_user.php?lang=".LANG,
        "ICON"  => "btn_new",
    ];
}
$APPLICATION->SetTitle(($ID>0? Loc::getMessage("LIBSITE_RESTAPI_EDIT").$ID : GetMessage("LIBSITE_RESTAPI_ADD")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
// создадим экземпляр класса административного меню
$context = new CAdminContextMenu($aMenu);
// выведем меню
$context->Show();
// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if($_REQUEST["mess"] == "ok" && $ID>0)
    CAdminMessage::ShowMessage(["MESSAGE"=>Loc::getMessage("LIBSITE_RESTAPI_SAVE"), "TYPE"=>"OK"]);

if($message)
    echo $message->Show();
elseif($rubric->LAST_ERROR!="")
    CAdminMessage::ShowMessage($rubric->LAST_ERROR);
?>
<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
    <?echo bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>">
    <?if($ID>0 && !$bCopy):?>
        <input type="hidden" name="ID" value="<?=$ID?>">
    <?endif;?>
    <?
    // отобразим заголовки закладок
    $tabControl->Begin();
    ?>
    <?
    //********************
    // первая закладка - форма редактирования параметров рассылки
    //********************
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td>LOGIN<span class="required">*</span></td>
        <td><input type="text" name="LOGIN" value="<?=$arDataForm['LOGIN']?>" size="30" maxlength="255"></td>
    </tr>
    <tr>
        <td>PASSWORD<span class="required">*</span></td>
        <td><input type="text" name="PASSWORD" value="<?=$arDataForm['PASSWORD']?>" size="30" maxlength="255"></td>
    </tr>
    <tr>
        <td>ACCESS_AREA <br>(через запятую, <br>без пробелов, если пусто то доступ ко всему)</td>
        <td><input type="text" name="ACCESS_AREA" value="<?=$arDataForm['ACCESS_AREA']?>" size="30" maxlength="255"></td>
    </tr>
    <?
    // завершение формы - вывод кнопок сохранения изменений
    $tabControl->Buttons(
        [
            "disabled"=>($POST_RIGHT<"W"),
            "back_url"=>"libsite_restapi_user.php?lang=".LANG,
        ]
    );
    ?>
    <?
    // завершаем интерфейс закладки
    $tabControl->End();
    ?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
