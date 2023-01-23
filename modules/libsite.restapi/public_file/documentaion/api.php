<?php

$template = json_decode(file_get_contents('lib/header.json'), true);
$arJson = [
    'lib/sertificate/get.json',
    'lib/sertificate/update.json'
];

foreach ($arJson as $json){
    $arTmpJson = json_decode(file_get_contents($json), true)['paths'];
    if(!empty($arTmpJson)){
        $template['paths'] = array_merge($template['paths'], $arTmpJson);
    }
}

header('Content-Type: application/json');
echo json_encode($template);