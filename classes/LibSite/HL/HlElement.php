<?php

namespace LibSite;

use Bitrix\Highloadblock\HighloadBlockTable as Hlblock,
    Bitrix\Main\Entity,
    Bitrix\Main\Application;

/**
 * Class HlElement - для работы с елементами хайлодов
 * @package LibSite
 */
class HlElement
{
    public $idHlbl;
    public $entity;
    public $entityDataClass;

    /**
     * HlElement constructor.
     * @param $hlBlockName string Название хайлода
     */
    public function __construct($hlBlockName)
    {
        //$this->idHlbl = $idHlbl;
        $this->hlBlockName = $hlBlockName;
        $this->entity = $this->getEntity();
        $this->entityDataClass = $this->getEntityDataClass();
    }

    /**
     * Получает сущность HL
     * @return false
     */
    private function getEntity(){
        if (empty($this->hlBlockName)) return false;
        $hlblock = Hlblock::getList(['filter' => ['NAME' => $this->hlBlockName] ])->fetch();
        $entity = Hlblock::compileEntity($hlblock);
        return $entity;
    }

    /**
     * Получает класс HL
     * @return false
     *
     */
    private function getEntityDataClass(){
        if (empty($this->entity)) return false;
        $entityDataClass = $this->entity->getDataClass();
        return $entityDataClass;
    }

    /**
     * Получает все поля включая списки с их значениями
     * @return array|false
     */
    public function getFields()
    {
        if (!$this->hlBlockName) {
            return false;
        }

        $db = Application::getConnection();
        $dbHelper = $db->getSqlHelper();

        $hlb = $db->query("
            SELECT
              *
            FROM `b_hlblock_entity`
            WHERE `NAME` = '" . $dbHelper->forSql($this->hlBlockName) . "';
        ")->fetch();

        $hlbFields = array();
        $hlbEnumerationFields = array();
        if ($hlb && $rsHlbFields = $db->query("
                SELECT
                  *
                FROM `b_user_field`
                WHERE `ENTITY_ID` = 'HLBLOCK_" . intval($hlb['ID']) . "'
            ")
        ) {
            while ($field = $rsHlbFields->fetch()) {
                $hlbFields['by_id'][ $field['ID'] ] = $field;
                $hlbFields['by_code'][ $field['FIELD_NAME'] ] =& $hlbFields['by_id'][ $field['ID'] ];

                if ($field['USER_TYPE_ID'] == 'enumeration') {
                    $hlbEnumerationFields[ $field['ID'] ] = $field['FIELD_NAME'];
                }
            }
        }

        if ($hlbEnumerationFields && $rsEnums = $db->query("
                SELECT
                  `ID`,
                  `USER_FIELD_ID`,
                  `VALUE`,
                  `XML_ID`
                FROM `b_user_field_enum`
                WHERE `USER_FIELD_ID` in ('" . implode("','", array_keys($hlbEnumerationFields)) . "')
                ORDER BY SORT
            ")
        ) {
            while ($enum = $rsEnums->fetch()) {
                $userFieldId = $enum['USER_FIELD_ID'];
                $hlbFields['by_id'][ $userFieldId ]['ENUMS'][ $enum['ID'] ] = $enum;
            }
        }

        if ($hlbFields) {
            return $hlbFields;
        }

        return false;
    }

    /**
     * Добавление в записи в HL + проверка на существование записи
     * @param $arData
     * @return false
     */
    public function addUnique($arData){
        $result = false;
        # поиск дубля
        $countItem = $this->entityDataClass::getList([
            'select'=>['ID'],
            'filter'=>$arData,
            'limit'=>1,
            'count_total'=>1
        ])->getCount();

        if($countItem===0){
            $addResult = $this->entityDataClass::add($arData);
            if($addResult->isSuccess()){
                $result = $addResult->getPrimary()['ID'];
            }
        }

        return $result;

    }

}