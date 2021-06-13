<?php

namespace FourPx\CarsAvaliable;

use \Bitrix\Main\Entity,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Type;

Loc::loadMessages(__FILE__);

class LogTable extends Entity\DataManager
{
    public static function getMapArray() {
        return array(
            'Id' => array(
                'type' => 'IntegerField',
                'primary' => true,
                'title' => 'ID',
                'autocomplete' => true,
                'adm_is_table_header' => true,
            ),
            'CountAll' => array(
                'type' => 'IntegerField',
                'title' => 'CountAll',
                'adm_is_filter_field' => true,
                'adm_is_table_header' => true,
            ),
            'CountUpdate' => array(
                'type' => 'IntegerField',
                'title' => 'CountUpdate',
                'adm_is_filter_field' => true,
                'adm_is_table_header' => true,
            ),
            'CountAdd' => array(
                'type' => 'IntegerField',
                'title' => 'CountAdd',
                'adm_is_filter_field' => true,
                'adm_is_table_header' => true,
            ),
            'CountDel' => array(
                'type' => 'IntegerField',
                'title' => 'CountDel',
                'adm_is_filter_field' => true,
                'adm_is_table_header' => true,
            ),
			'date_create' => array(
				'type' => 'DatetimeField',
				'title' => 'dateImport',
				'adm_is_filter_field' => true,
				'adm_is_table_header' => true,
			),
			'hash' => array(
				'type' => 'StringField',
				'title' => 'hash',
				'adm_is_filter_field' => true,
				'adm_is_table_header' => true,
				'size' => 32,
			),
            'comment' => array(
                'type' => 'StringField',
                'title' => 'comment',
                'size' => 32,
            ),
            'finishImport' => array(
                'type' => 'BooleanField',
                'title' => 'finishImport',
            ),
            'DATE_UPDATE' => array(
                'type' => 'DatetimeField',
                'title' => 'DATE_UPDATE',
            ),
        );
    }

    public static function getTableName()
    {
        return 'fourpx_cars_avaliable_log';
    }

    public static function getMap()
    {
        $arMap = array();

        foreach (self::getMapArray() as $fieldName => $fieldProps) {

            $entityClass = '\\Bitrix\\Main\\Entity\\' . $fieldProps['type'];
            $arMap[] = new $entityClass($fieldName, $fieldProps);
        }

        return $arMap;
    }

    public static function onBeforeAdd(Entity\Event $event)
    {
        $result = new Entity\EventResult;

        $arModifiedFields = array();

        $arModifiedFields['date_create'] = new Type\DateTime();

        $result->modifyFields($arModifiedFields);

        return $result;
    }

    public static function onBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;

        $arModifiedFields = array();

		$arModifiedFields['date_update'] = new Type\DateTime();

        $result->modifyFields($arModifiedFields);

        return $result;
    }

}