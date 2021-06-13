<?
namespace FourPx;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Application,
    \Bitrix\Highloadblock\HighloadBlockTable;

class HLBlock {

    public static function getList($hlBlockName = '', $arParams = array(), $keyField = false)
    {
        $result = false;

        if (Loader::includeModule('highloadblock')) {

            $hlBlockEntity = \Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['NAME' => $hlBlockName] ])->fetch();

            if ($keyField && ! in_array($keyField, $arParams['select'])) {
                $arParams['select'][] = $keyField;
            }

            if ($hlBlockEntity) {
                $hlBlock = HighloadBlockTable::compileEntity($hlBlockEntity)->getDataClass();

                if ($rs = $hlBlock::getList($arParams)) {
                    $arList = array();
                    if ($keyField) {
                        while ($record = $rs->fetch()) {
                            $arList[ $record[$keyField] ] = $record;
                        }
                    } else {
                        while ($record = $rs->fetch()) {
                            $arList[] = $record;
                        }
                    }
                    $result = $arList;
                }
            }
        }

        return $result;
    }

    /*
     * возвращает список полей со значениями enum (тип список) в том числе
     */
    public static function getFields($hlBlockName = '')
    {
        if (! $hlBlockName) {
            return false;
        }

        $db = Application::getConnection();
        $dbHelper = $db->getSqlHelper();

        $hlb = $db->query("
            SELECT
              *
            FROM `b_hlblock_entity`
            WHERE `NAME` = '" . $dbHelper->forSql($hlBlockName) . "';
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

    public static function add($hlBlockName = '', $arData = array())
    {
        $result = false;

        # поиск дубля
        $item = array_pop(self::getList($hlBlockName, array('filter' => $arData, 'select' => array('ID'))));

        if (! $item) {
            if (Loader::includeModule('highloadblock')) {

                $hlBlockEntity = \Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['NAME' => $hlBlockName] ])->fetch();


                if ($hlBlockEntity) {
                    $hlBlock = HighloadBlockTable::compileEntity($hlBlockEntity)->getDataClass();

                    if ($rs = $hlBlock::add($arData)) {
                        $result = $rs->getId();
                    }
                }
            }
        } else {
            $result = $item['ID'];
        }

        return $result;
    }

    /**
     * Обновление полей HL-элемента
     * */
    public static function update($hlBlockName, $elId, $arData = array()){
        if (!Loader::includeModule('highloadblock')) return false;

        $result = false;

        $hlBlockEntity = HighloadBlockTable::getList(['filter' => ['NAME' => $hlBlockName] ])->fetch();

        if ($hlBlockEntity) {
            $hlBlock = HighloadBlockTable::compileEntity($hlBlockEntity)->getDataClass();

            if ($rs = $hlBlock::update($elId, $arData)) {
                $result = $rs->isSuccess();
            }
        }

        return $result;
    }


    public static function isHlExists($hlName)
    {
        if (!Loader::includeModule('highloadblock')) return false;

        $hlBlockEntity = \Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['NAME' => $hlName]])->fetch();

        if ($hlBlockEntity === false) {
            return false;
        }

        return true;
    }


    /**
     * Создание нового HLBlock
     *
     * @param $hlName
     * @param $arFields
     * @return array|false|int
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public static function createHl($hlName, $arFields)
    {
        if (! Loader::includeModule('highloadblock')) return false;

        $rsHlCreate = \Bitrix\Highloadblock\HighloadBlockTable::add([
            'NAME' => $hlName,
            'TABLE_NAME' => 'hl_' . self::camelToSnake($hlName)
        ]);

        if ($rsHlCreate->isSuccess()) {
            $hlId = $rsHlCreate->getId();

            \Bitrix\Highloadblock\HighloadBlockLangTable::add([
                'ID' => $hlId,
                'LID' => 'ru',
                'NAME' => $hlName
            ]);

            \Bitrix\Highloadblock\HighloadBlockLangTable::add([
                'ID' => $hlId,
                'LID' => 'en',
                'NAME' => $hlName
            ]);


            $userField = new \CUserTypeEntity;
            foreach ($arFields as $fieldName => $fieldType) {
                $arField = [
                    'ENTITY_ID' => 'HLBLOCK_' . $hlId,
                    'FIELD_NAME' => 'UF_' . self::camelToSnake($fieldName, 'upper'),
                    'USER_TYPE_ID' => $fieldType,
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru' => $fieldName, 'en' => $fieldName),
                    "LIST_COLUMN_LABEL" => Array('ru' => $fieldName, 'en' => $fieldName),
                    "LIST_FILTER_LABEL" => Array('ru' => $fieldName, 'en' => $fieldName),
                    "ERROR_MESSAGE" => Array('ru' => $fieldName, 'en' => $fieldName),
                    "HELP_MESSAGE" => Array('ru' => '', 'en' => ''),
                ];

                $userField->Add( $arField );
            }
        }

        return isset($hlId) ? $hlId : false;
    }



    public static function prepareFieldsToCreateHlFromData($arData)
    {
        # формирование карточки со всеми заполенными полями. для определенеия типов полей
        $arFullCard = [];
        foreach ($arData as $card) {
            $hasEmpty = false;

            foreach ($card as $cardFieldName => $cardFieldValue) {
                if (empty($arFullCard[ $cardFieldName ])) {
                    if (empty( $cardFieldValue )) {
                        $arFullCard[ $cardFieldName ] = $cardFieldValue;
                        $hasEmpty = true;
                    } else {
                        $arFullCard[ $cardFieldName ] = $cardFieldValue;
                    }
                }
            }

            if (! $hasEmpty) {
                break;
            }
        }
        if (! empty($arFullCard)) {
            $arFullCard['Hash'] = 'string';
        }

        $arFields = [];
        foreach ($arFullCard as $fieldName => $fieldValue) {
            $fieldType = 'string';
            if (strpos($fieldName, 'Id') !== false
                && (empty($fieldValue) || is_int($fieldValue))
            ) {
                $fieldType = 'integer';
            }
            if (in_array($fieldValue, ['true', 'false'], true)) $fieldType = 'boolean';

            $arFields[ $fieldName ] = $fieldType;
        }

        return $arFields;
    }


    public static function fillHlWithData($hlName, $data)
    {
        $hlData = self::getList($hlName, [
            'select' => ['ID', 'UF_HASH']
        ],'UF_ID');


        foreach ($data as $row) {
            $hash = md5( serialize( $row ) );

            if ($hlItem = $hlData[ $row['Id'] ]) {

                if ($hlItem['UF_HASH'] !== $hash) {
                    foreach ($row as $fieldName => $fieldValue) {
                        $arFields[ 'UF_' . self::camelToSnake($fieldName, 'upper') ] = is_array($fieldValue) ? json_encode($fieldValue, JSON_UNESCAPED_UNICODE) : $fieldValue;
                    }
                    $arFields['UF_HASH'] = $hash;

                    self::update($hlName, $hlItem['ID'], $arFields);
                }
            } else {
                foreach ($row as $fieldName => $fieldValue) {
                    $arFields[ 'UF_' . self::camelToSnake($fieldName, 'upper') ] = is_array($fieldValue) ? json_encode($fieldValue, JSON_UNESCAPED_UNICODE) : $fieldValue;
                }
                $arFields['UF_HASH'] = $hash;

                self::add($hlName, $arFields);
            }
        }
    }


    /**
     * Преобразование верблюжьей нотации к змеиной
     * @param $name
     */
    private static function camelToSnake($name, $textType = "lower")
    {
        if ($textType === 'upper') {
            return trim(
                strtoupper(
                    preg_replace('/([A-Z])/', '_$1', $name)
                ),
                '_');
        }

        return trim(
            strtolower(
                preg_replace('/([A-Z])/', '_$1', $name)
            ),
            '_');
    }

}

?>
