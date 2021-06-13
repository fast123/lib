<?
namespace FourPx;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Application,
    \Bitrix\Highloadblock\HighloadBlockTable,
    \Bitrix\Iblock\IblockTable;

class IBlock
{

    public static function isIblockExists($ibCode)
    {
        Loader::includeModule('iblock');

        if(empty($ibCode)) {
            return false;
        }

        $rsIblock = \Bitrix\Iblock\ElementTable::getList([
            'filter' => [
                'CODE' => $ibCode
            ],
            'limit' => 1
        ]);

        if ($rsIblock->getSelectedRowsCount() === 1) {
            return true;
        }

        return false;
    }

    public static function createIblock($ibCode, $arProps)
    {
        $result = [
            'status' => 'success',
        ];

        Loader::includeModule('iblock');

        # создение типа инфоблока
        $ibType = new \CIBlockType;
        $ibTypeId = $ibType->Add([
            'ID' => $ibCode,
            'SECTIONS' => 'Y',
            'IN_RSS' => 'N',
            'SORT' => '100',
            'LANG' => [
                'ru' => [
                    'NAME' => $ibCode,
                    'SECTION_NAME' => 'Раздел',
                    'ELEMENT_NAME' => 'Автомобиль'
                ],
                'en' => [
                    'NAME' => $ibCode,
                    'SECTION_NAME' => 'Раздел',
                    'ELEMENT_NAME' => 'Автомобиль'
                ]
            ]
        ]);

        if (! $ibTypeId) {
            $result['status'] = 'error';
            $result['message'] = $ibType->LAST_ERROR;

            return $result;
        }


        # создание инфоблока
        $ib = new \CIBlock;
        $ibId = $ib->Add([
            'ACTIVE' => 'Y',
            'NAME' => 'Сток автомобилей',
            'CODE' => $ibCode,
            'API_CODE' => $ibCode,
            'LIST_PAGE_URL' => '/catalog/',
            'DETAIL_PAGE_URL' => '/catalog/#ELEMENT_CODE#/',
            'IBLOCK_TYPE_ID' => $ibCode,
            'XML_ID' => $ibCode,
            'SITE_ID' => ['s1'],
            'SORT' => 100,
            'GROUP_ID' => ['2' => 'R']
        ]);

        if (! $ibId) {
            $result['status'] = 'error';
            $result['message'] = $ib->LAST_ERROR;

            return $result;
        }


        # создание свойств
        $ibp = new \CIBlockProperty;
        foreach ($arProps as $propCode => $propType) {
            $newProp = [
                'NAME' => $propCode,
                'ACTIVE' => 'Y',
                'SORT' => '100',
                'CODE' => $propCode,
                'IBLOCK_ID' => $ibId,
                'MULTIPLE' => 'N',
                'PROPERTY_TYPE' => 'S'
            ];

            if ($propType === 'number') {
                $newProp['PROPERTY_TYPE'] = 'N';
            } elseif ($propType === 'text') {
                $newProp['PROPERTY_TYPE'] = 'S';
                $newProp['USER_TYPE'] = 'HTML';
            }

            $ibpId = $ibp->Add( $newProp );

            if (! $ibpId) {
                $result['status'] = 'error';
                $result['message'] = $ibp->LAST_ERROR;
            }
        }

        return $result;
    }

    public static function prepareFieldsToCreateIBlockFromData($arData)
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
                $fieldType = 'number';
            }
            if (in_array($fieldValue, ['true', 'false'], true)) $fieldType = 'boolean';
            if (is_array($fieldValue)) {
                $fieldType = 'text';
            }

            $arFields[ $fieldName ] = $fieldType;
        }

        return $arFields;
    }


    /**
     * @param $ibCode
     * @param array $data
     * @param string $keyField ключ внешнего спраочника
     * @param string $nameField ключ внешнего спраочника
     * @param array $arFilter фильтр GetList для ограничения выборки для ограничения выборк
     * @throws \Bitrix\Main\LoaderException
     */
    public static function fillIbWithData($ibCode, $data, $keyField, $nameField, $arFilter = [])
    {

        Loader::includeModule('iblock');

        $ibId = self::getIblockIdByCodes($ibCode);

        # получение существующих данных из IB
        $rsIbStock = \CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'IBLOCK_ID', 'ID'
            ]
        );


        $arIbStock = [];
        while ($ibStockItem = $rsIbStock->GetNextElement()) {

            $fields = $ibStockItem->GetFields();
            $props = $ibStockItem->GetProperties();

            $arIbStock[ $props[ $keyField ]['VALUE'] ] = [
                'ID' => $fields['ID'],
                'HASH' => $props['Hash']['VALUE'],
                'IMAGES' =>$props['images']
            ];
        }

        # наполнение
        $ibElement = new \CIBlockElement;

        $arFields = [
            'IBLOCK_ID' => $ibId,
            'ACTIVE' => 'Y'
        ];

        foreach ($data as $row) {
            $hash = md5( serialize( $row ) );

            $arFields['NAME'] = $row[ $nameField ].' '.$row['model'];
            $arFields['PROPERTY_VALUES'] = [];


            if ($ibItem = $arIbStock[ $row[ $keyField ] ]) {
                if ($ibItem['HASH'] !== $hash) {
                    foreach ($row as $fieldName => $fieldValue) {
                        if($fieldName == 'images') {

                            if(is_array($fieldValue['image'])){
                                $arFields['PROPERTY_VALUES'][ $fieldName] = $fieldValue['image'];
                            }
                            else{
                                $arFields['PROPERTY_VALUES'][ $fieldName] = [ $fieldValue['image'] ];
                            }
                            continue;
                        }

                        if($fieldName == 'description'){
                            $arFields['DETAIL_TEXT'] = $fieldValue;
                            continue;
                        }

                        $arFields['PROPERTY_VALUES'][ $fieldName ] = is_array($fieldValue) ? json_encode($fieldValue, JSON_UNESCAPED_UNICODE) : $fieldValue;
                    }
                    $arFields['PROPERTY_VALUES']['Hash'] = $hash;

                    #TODO изображения
                    $arFields['PROPERTY_VALUES']['images'] = self::updateFilePropertyValues(
                        $ibItem['ID'],
                        $ibItem['IMAGES'],
                        $arFields['PROPERTY_VALUES']['images'],
                        'images'
                    );
                    /*$arFields["DETAIL_PICTURE"] = $arFields['PROPERTY_VALUES']['images'][0]['VALUE'];
                    $arFields["PREVIEW_PICTURE"] = $arFields["DETAIL_PICTURE"];*/

                    $ibElement->Update($ibItem['ID'], $arFields);
                }
            } else {
                foreach ($row as $fieldName => $fieldValue) {

                    if($fieldName == 'images') {

                        if(is_array($fieldValue['image'])){
                            $arFields['PROPERTY_VALUES'][ $fieldName] = $fieldValue['image'];
                        }
                        else{
                            $arFields['PROPERTY_VALUES'][ $fieldName] = [ $fieldValue['image'] ];
                        }
                        continue;
                    }

                    if($fieldName == 'description'){
                        $arFields['DETAIL_TEXT'] = $fieldValue;
                        continue;
                    }

                    $arFields['PROPERTY_VALUES'][ $fieldName] = is_array($fieldValue) ? json_encode($fieldValue, JSON_UNESCAPED_UNICODE) : $fieldValue;
                }
                $arFields['PROPERTY_VALUES']['Hash'] = $hash;

                #TODO изображения
                $arFields['PROPERTY_VALUES']['images'] = self::getFilePropertyValue($arFields['PROPERTY_VALUES']['images']);
                $arFields["DETAIL_PICTURE"] = $arFields['PROPERTY_VALUES']['images'][0]['VALUE'];
                $arFields["PREVIEW_PICTURE"] = $arFields["DETAIL_PICTURE"];

                $ibElement->Add($arFields);
            }
        }
    }


    /**
     *  Получение ID инфоблока по его коду
     *
     *  @param array $arIblockCodes код или массив кодов
     */
    public static function getIblockIdByCodes($iblockCodes) {
        $result = array();

        if (Loader::includeModule('iblock')) {
            $rsIblocks = IblockTable::getList(
                array(
                    'filter' => array(
                        'CODE' => $iblockCodes
                    ),
                    'select' => array(
                        'ID',
                        'CODE'
                    )
                )
            );

            while ($iblock = $rsIblocks->fetch()) {
                $result[ $iblock['CODE'] ] = $iblock['ID'];
            }

            if (! is_array( $iblockCodes ) && is_array( $result )) {
                $result = array_pop($result);
            }
        }

        return $result;
    }


    public static function  updateFilePropertyValues($idElement, $arrIBFile, $arrFeedFile, $propertyCode)
    {
        Loader::includeModule('iblock');
        $arrDeleteFile = [];
        $arrNotNeedUpdateFile = [];
        $elementObject = new \CIBlockElement;
        foreach ($arrIBFile['DESCRIPTION'] as $key=>$value) {
            if (!in_array($value, $arrFeedFile)) {
                $arrDeleteFile[$arrIBFile['PROPERTY_VALUE_ID'][$key]] = array('del' => 'Y', 'tmp_name' => '');
            } else {
                $arrNotNeedUpdateFile[] = $value;
            }
        }

        if (!empty($arrDeleteFile)) {
            $elementObject->SetPropertyValueCode($idElement, $propertyCode, $arrDeleteFile);
        }

        $fileValue = [];
        if (is_array($arrFeedFile)) {
            foreach ($arrFeedFile as $url) {
                if (!in_array($url, $arrNotNeedUpdateFile))
                    $fileValue[] = ['VALUE' => \CFile::MakeFileArray($url), 'DESCRIPTION' => $url];
            }
        } else {
            if (!in_array($arrFeedFile, $arrNotNeedUpdateFile))
                $fileValue[] = ['VALUE' => \CFile::MakeFileArray($arrFeedFile), 'DESCRIPTION' => $arrFeedFile];
        }

        return $fileValue;
    }

    public static function getFilePropertyValue($arrUrl)
    {
        foreach ($arrUrl as $url) {
            $fileValue[] = ['VALUE' => \CFile::MakeFileArray($url), 'DESCRIPTION' => $url];
        }
        return $fileValue;
    }

    public static function getIBElements($IBLOCK_ID)
    {
        $rsIbElements = \CIBlockElement::GetList(
            array(),
            array(
                'IBLOCK_ID' => $IBLOCK_ID,
            ),
            false,
            false,
            array('ID', 'CODE', 'IBLOCK_ID', 'PROPERTY_HASH', 'PROPERTY_IMAGES')
        );

        $arIbElements = [];
        while ($ibElement = $rsIbElements->GetNextElement()) {
            $props = $ibElement->GetProperties();
            $fields = $ibElement->GetFields();
            $arIbElements[ $fields['CODE'] ] = array(
                'ID' => $fields['ID'],
                'HASH' => $props['HASH']['VALUE'],
                'IMAGES' => $props['IMAGES'],
            );
        }

        return $arIbElements;
    }
}
