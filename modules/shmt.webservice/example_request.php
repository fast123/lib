<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__));
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

use Bitrix\Main\Web\HttpClient;

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('CHK_EVENT', true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$http = new HttpClient(['socketTimeout'=>10]);
$arRequest = [
    'url'=>'https://ru.hisense.com/',
    'user_login'=>'trofimov.aleksandr@4px.ru',
    'user_webhook_token'=>'',//сгенерированый токен веб хука,
    'full_webservice_method_name'=>'/test.test_logic/'
];
$result = $http->get(
    $arRequest['url'].
    $arRequest['user_login'].'/'.
    $arRequest['user_webhook_token'].
    $arRequest['full_webservice_method_name']
);

$result = json_decode($result);



