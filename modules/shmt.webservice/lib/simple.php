<?php


namespace Shmt\Webservice;

use \Bitrix\Main\EventManager;

class Simple extends \IRestService
{
    const SCOPE = 'test';//что-то типо пространства имен
    #методы нужно подключить тут обработчик событий
    #не забыть добавить обработчик в init.php
    #$eventManager->addEventHandler('rest', 'OnRestServiceBuildDescription', ['\LibSite\Rest\WebService', 'OnRestServiceBuildDescription']);
    public static function OnRestServiceBuildDescription(){
        return [
            static::SCOPE => [
                static::SCOPE . '.test_logic' => [
                    'callback' =>  [__CLASS__, 'testLogic'],
                    'options' => []
                ],
                static::SCOPE . '.test_logic2' => [
                    'callback' =>  [__CLASS__, 'testLogic2'],
                ]
            ]
        ];
    }

    #методы нужно подключить и тут
    public function getDescription(){
        return [
            static::SCOPE => [
                static::SCOPE . '.test_logic' => [
                    'callback' =>  [__CLASS__, 'testLogic'],
                ],
                static::SCOPE . '.test_logic2' => [
                    'callback' =>  [__CLASS__, 'testLogic2'],
                ]
            ]
        ];
    }

    #методы котрые вызвает клиент
    public function testLogic(){
        return 'test';
    }
    public function testLogic2(){
        return 'test2';
    }
}