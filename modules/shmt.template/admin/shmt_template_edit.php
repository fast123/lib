<?
//TODO документация https://dev.1c-bitrix.ru/api_help/main/general/admin.section/rubric_edit.php
use Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc;



require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('libsite.restapi');
$moduleId = 'libsite.restapi';
$userRights = $APPLICATION->GetUserRight($moduleId);
$request = Application::getInstance()->getContext()->getRequest();
$urlEdit = '/bitrix/admin/libsite_restapi_user_edit.php';
\CJSCore::Init(array('jquery'));

if ($userRights <= 'D') die('Доступ к модулю запрещён.');
?>

<?
// здесь будет вся серверная обработка и подготовка данных
?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
?>
<?
// здесь будет вывод страницы с формой
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
