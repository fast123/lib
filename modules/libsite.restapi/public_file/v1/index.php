<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('libsite.restapi');
require_once (__DIR__ . '/../autoload.php');

use LibSite\RestApi\Pecee\SimpleRouter\SimpleRouter as Router;
require_once (__DIR__ . '/../config/routes.php');

try {
    Router::start();
} catch (Throwable $e) {

}