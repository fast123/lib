<?php
namespace LibSite;

use \Bitrix\Main\Loader;
use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Entity;


class Subscribe
{
    protected static $instance = null;
    private $errors = array();
    private $entity = false;

    const HL_BLOCK_ENTITY_NAME = "Subscribe";

    function __construct()
    {
        if (!Loader::includeModule("highloadblock")) {
            throw new Exception("module highloadblock not installed");
            return;
        }

        $this->CheckStorage();
    }

    public static function getInstance()
    {
        if (!isset(static::$instance))
            static::$instance = new static();

        return static::$instance;
    }

    public function CheckStorage()
    {
        $result = HL\HighloadBlockTable::getList(array("filter" => array("=NAME" => self::HL_BLOCK_ENTITY_NAME)));
        if ($row = $result->fetch()) {
            $this->HLBLOCK_ID = $row["ID"];
        } else {
            $data = array(
                "NAME" => self::HL_BLOCK_ENTITY_NAME,
                "TABLE_NAME" => "hl_" . strtolower(self::HL_BLOCK_ENTITY_NAME)
            );
            $result = HL\HighloadBlockTable::add($data);
            if ($result->isSuccess()) {
                $this->HLBLOCK_ID = $result->getId();

                foreach ($this->GetStorageFields() as $arField) {
                    $oUserTypeEntity = new \CUserTypeEntity();

                    $iUserFieldId = $oUserTypeEntity->Add($arField);
                }
            }
        }
    }

    private function GetStorageFields()
    {
        $ID = $this->HLBLOCK_ID;
        $arFields = array(
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $ID,
                'FIELD_NAME' => 'UF_SUBSCRIBE_ID',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'I',
                'EDIT_FORM_LABEL' => array(
                    'ru' => 'Id подписки',
                    'en' => 'Subscribe id',
                )
            ),
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $ID,
                'FIELD_NAME' => 'UF_SUBSCRIBER_NAME',
                'USER_TYPE_ID' => 'string',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'S',
                'EDIT_FORM_LABEL' => array(
                    'ru' => 'Имя',
                    'en' => 'Name',
                )
            ),
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $ID,
                'FIELD_NAME' => 'UF_SUBSCRIBER_PHONE',
                'USER_TYPE_ID' => 'string',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'S',
                'EDIT_FORM_LABEL' => array(
                    'ru' => 'Телефон',
                    'en' => 'Phone',
                )
            )
        );

        return $arFields;
    }

    private function GetEntity()
    {
        if (!$this->entity) {
            $hlblock = HL\HighloadBlockTable::getById($this->HLBLOCK_ID)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $this->entity = $entity->getDataClass();
        }
        return $this->entity;
    }

    public function GetList($arSort = array(), $arFilter = array(), $limit = false, $arSelect = array("*"))
    {
        if (empty($arSort))
            $arSort = array("UF_SUBSCRIBE_ID" => "asc");
        $entity = $this->GetEntity();
        $dbRes = $entity::getList(array('select' => $arSelect, 'order' => $arSort, 'filter' => $arFilter, 'limit' => $limit));

        return $dbRes->fetchAll();
    }

    public function GetByID($ID)
    {
        $entity = $this->GetEntity();
        $arResult = $entity::getList(array('filter' => array('UF_SUBSCRIBE_ID' => $ID), 'select' => array('*')))->fetch();

        if (!is_array($arResult)) $arResult = array();

        return $arResult;
    }

    public function Add($arFields)
    {
        global $APPLICATION;
        $APPLICATION->ResetException();
        $this->errors = array();

        $entity = $this->GetEntity();

        $dbRes = $entity::add($arFields);
        if (!$dbRes->isSuccess()) {
            $error = '';
            if ($dbRes->getErrors()) {
                foreach ($dbRes->getErrors() as $errorObj) {
                    $this->errors[] = $errorObj->getMessage();
                }
            }
            return false;
        } else {
            $ID = $dbRes->getId();
            return $ID;
        }
    }

    public function Update($ID, $arFields)
    {
        global $APPLICATION;
        $this->errors = array();
        $APPLICATION->ResetException();

        if (empty($arFields)) {
            $APPLICATION->throwException("Не указаны поля");
            return false;
        }

        $entity = $this->GetEntity();
        $dbRes = $entity::update($ID, $arFields);
        if (!$dbRes->isSuccess()) {
            $error = '';
            if ($dbRes->getErrors()) {
                foreach ($dbRes->getErrors() as $errorObj) {
                    $this->errors[] = $errorObj->getMessage();
                }
            }
            return false;
        } else {
            $ID = $dbRes->getId();
            return $ID;
        }
    }

    public function Delete($ID)
    {
        $entity = $this->GetEntity();
        return $entity::delete($ID);

    }

    public function GetErrors()
    {
        if (!isset($this->errors) || !is_array($this->errors)) $this->errors = array();
        return implode('<br>', array_unique($this->errors));
    }
}
