<?php


namespace LibSite\Iblock;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Application,
    \Bitrix\Highloadblock\HighloadBlockTable,
    \Bitrix\Iblock\IblockTable;

class IbElement
{
    /**
     * Обновление файлов в свойстве
     *
     * @param $idElement ид элемента инфоблока
     * @param $arrIBFile array возвращаемы битриксом существующих фалов
     * @param $arrFeedFile array внешнх ссылок на файл для загрузки
     * @param $propertyCode string сивольный код свойства в котором хранятся файлы
     * @return array
     */
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
     * Формирование масива для загрузки файлов в свойство инфоблока
     *
     * @param $arrUrl array ссылок на файлы
     * @return array
     */
    public static function getFilePropertyValue($arrUrl)
    {
        foreach ($arrUrl as $url) {
            $fileValue[] = ['VALUE' => \CFile::MakeFileArray($url), 'DESCRIPTION' => $url];
        }
        return $fileValue;
    }
}