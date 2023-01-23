<?php

namespace LibSite\RestApi;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;

//Loc::loadMessages(__FILE__);

class  UsersTable extends DataManager
{
    public static function getTableName()
    {
        return 'libsite_restapi_users';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new StringField('LOGIN', array(
                'required' => true,
                'title' => 'LOGIN',
                'validation' => function() {
                    return array(
                        new Validator\Unique()
                    );
                }
            )),
            new StringField('PASSWORD', array(
                'required' => true,
                'title' => 'PASSWORD',
            )),
            new StringField('ACCESS_AREA', array(
                'title' => 'ACCESS_AREA',
            )),
        );
    }
}
