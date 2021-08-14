<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Loader;

//Loc::loadMessages(__FILE__);


$data = array(
    'NAME' => 'название_платежной_системы',
    'SORT' => 100,
    'CODES' => array(
        "TEST_GATE_LOGIN" => array(
            "NAME" => 'Логин',
            "DESCRIPTION" => 'Логин',
            'SORT' => 100,
            'GROUP' => 'Параметры подключения платежного шлюза',
        ),
        "TEST_GATE_TEST_MODE" => array(
            "NAME" => "Тестовый режим",
            "DESCRIPTION" => 'Если отмечено, плагин будет работать в тестовом режиме. При пустом значении будет стандартный режим работы.',
            'SORT' => 130,
            'GROUP' => 'Параметры подключения платежного шлюза',
            "INPUT" => array(
                'TYPE' => 'Y/N'
            ),
            'DEFAULT' => array(
                "PROVIDER_VALUE" => "N",
                "PROVIDER_KEY" => "INPUT"
            )
        ),
        "TEST_FFD_VERSION" => array(
            "NAME" => 'Формат фискальных документов',
            "DESCRIPTION" => 'Формат версии требуется указать в личном кабинете банка и в кабинете сервиса фискализации',
            'SORT' => 400,
            'GROUP' => 'Настройки ФФД',
            'TYPE' => 'SELECT',
            'INPUT' => array(
                'TYPE' => 'ENUM',
                'OPTIONS' => array(
                    '1.00' => '1.00',
                    '1.05' => '1.05',
                )
            ),
            'DEFAULT' => array(
                "PROVIDER_VALUE" => "1.05",
                "PROVIDER_KEY" => "INPUT"
            )
        ),

    )
);