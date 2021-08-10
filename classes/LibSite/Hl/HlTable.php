<?
namespace LibSite\Hl;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Application,
    \Bitrix\Highloadblock\HighloadBlockTable;

class HlTable {


    /**
     * Создание нового HLBlock
     *
     * @param $hlName
     * @param $arFields
     * @return array|false|int
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public static function create($hlName, $arFields)
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

}

?>
