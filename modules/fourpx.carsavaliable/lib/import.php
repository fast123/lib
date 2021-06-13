<?php

namespace FourPx\CarsAvaliable;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Import
{
	public $moduleId;
	public $optionsModule;
    public $feedIsUpdate;
    public $feedDataArray;
    public $feedHash;
    public $allIBProperties;
    public $elementObject;
    public $cronMode = false;
    public $lastImport;
    public $logIsEmpty;

	public function __construct()
    {
        $this->moduleId = 'fourpx.carsavaliable';
        $this->optionsModule = Option::getForModule($this->moduleId);
        $feedFileString = file_get_contents($this->optionsModule["link_xml"]);

        $simpleXmlObj = simplexml_load_string($feedFileString);
        $simpleXmlArray = json_decode(json_encode($simpleXmlObj->cars), TRUE);
        $this->feedDataArray = $simpleXmlArray['car'];

        $this->feedHash = md5($feedFileString);

        $logImport = LogTable::getList(array('select' => array('*'), 'order' => array('Id')));
        while ($log = $logImport->fetch()) {
            $logData[] = $log;
        }
        $this->lastImport = end($logData);
        $this->logIsEmpty = !isset($this->lastImport);
        $this->feedIsUpdate = !($this->feedHash === $this->lastImport["hash"]);
    }

    public static function startImport()
    {
        \FourPx\Helper::writeToLog('import.html', 'start_cron_import');
        $obj = new self();
        $obj->cronMode = true;
        $obj->uploadToIBlock();
        \FourPx\Helper::writeToLog('import.html', 'end_cron_import');
        return '\FourPx\CarsAvaliable\Import::startImport();';
    }

    public function uploadToIBlock()
    {
        ignore_user_abort(true);
        set_time_limit(0);

        $countAll = 0;
        $countUpdate = 0;
        $countAdd = 0;
        $countDel = 0;

        $iBlockId = $this->optionsModule["iblock"];
        if (!isset($iBlockId) or $iBlockId === "false") return false;

        if (!$this->lastImport['finishImport'] and !$this->logIsEmpty) {
            //Проверка идет ли импорт.
            //Добавить проверку по времени. Если импорт был слишком давно, то попробовать снова.
            if(!$this->cronMode) {
                $arrMessage = array(
                    "MESSAGE" => GetMessage('IMPORT_IS_NOT_FINISH'),
                    "DETAILS" => GetMessage('IMPORT_IS_NOT_FINISH_DETAIL'),
                    "HTML" => true,
                );
                $msg = new \CAdminMessage($arrMessage);
                $return['HTML'] = $msg->Show();
                $return['DATA'] = $this->lastImport;
                return json_encode($return, JSON_UNESCAPED_UNICODE);
            } else {
                return false;
            }
        }

        $logAdd = LogTable::add(
            array(
                'comment' => GetMessage('COMMENT_START_UPDATE'),
                'CountAll' => count($this->feedDataArray),
                'CountUpdate' => $countUpdate,
                'CountAdd' => $countAdd,
                'CountDel' => $countDel,
                'dateImport' => time(),
                'finishImport' => 0,
                'hash' => $this->feedHash,
            )
        );
        $logId = $logAdd->getId();

        if ($this->feedIsUpdate) {
            Loader::includeModule('iblock');

            $this->elementObject = new \CIBlockElement;

            $arIbCars = $this->getIBElements();
            $endElementIb = end($arIbCars);
            if (!isset($endElementIb['HASH']) && count($this->feedDataArray)>0) {
                Properties::setRequiredProperties($iBlockId);
            }

            $elementIds = [];
            foreach ($this->feedDataArray as $id=>$data) {
                $hash = md5(serialize($data));

                $name = $data['mark_id'] . ' ' . $data['folder_id'];
                $codeEl = $data['unique_id'];
                $description = $data['description'];

                $properties = $this->parseProperties($data);
                $properties['HASH'] = $hash;

                $uploadArray = [
                    "MODIFIED_BY" => 1,
                    "IBLOCK_SECTION_ID" => false,
                    "PROPERTY_VALUES"=> $properties,
                    "NAME" => $name,
                    "PREVIEW_TEXT" => $description,
                    "DETAIL_TEXT" => $description,
                    "ACTIVE" => "Y",
                ];
                $arrUrlImg = $data['images']['image'];

                if (!isset($arIbCars[$codeEl])) {
                    //add element
                    $uploadArray['PROPERTY_VALUES']['IMAGES'] = $this->getFilePropertyValue($arrUrlImg);
                    $uploadArray["DETAIL_PICTURE"] = $uploadArray['PROPERTY_VALUES']['IMAGES'][0]['VALUE'];
                    $uploadArray["PREVIEW_PICTURE"] = $uploadArray["DETAIL_PICTURE"];
                    $uploadArray["IBLOCK_ID"] = $iBlockId;
                    $uploadArray["CODE"] = $codeEl;
                    $elementIds[] = ($this->elementObject->Add($uploadArray)) ? 'Success add' : "Error: ".$this->elementObject->LAST_ERROR;
                    $countAdd++;
                } elseif ($arIbCars[$codeEl]["HASH"] != $hash) {
                    //update element
                    $elementId = $arIbCars[$codeEl]["ID"];
                    $uploadArray['PROPERTY_VALUES']['IMAGES'] = $this->updateFilePropertyValues(
                        $elementId,
                        $arIbCars[$codeEl]["IMAGES"],
                        $arrUrlImg,
                        'IMAGES'
                    );
                    $elementIds[] = ($this->elementObject->update($elementId, $uploadArray)) ? 'Success update' : "Error: ".$this->elementObject->LAST_ERROR;
                    $countUpdate++;
                }
                unset($arIbCars[$codeEl]);
                $countAll++;
            }

            foreach ($arIbCars as $arr) {
                \CIBlockElement::Delete($arr['ID']);
            }
            $comment = GetMessage('COMMENT_SUCCESS_UPDATE');
        } else {
            $comment = GetMessage('COMMENT_NO_UPDATE');
        }

        LogTable::update($logId,
            array(
                'comment' => $comment,
                'CountAll' => $countAll,
                'CountUpdate' => $countUpdate,
                'CountAdd' => $countAdd,
                'CountDel' => $countDel,
                'dateImport' => time(),
                'finishImport' => 1,
            )
        );

        if(!$this->cronMode) {
            $arrMessage = array(
                "MESSAGE" => GetMessage('SUCCESS_IMPORT'),
                "DETAILS" => GetMessage('COMMENT_COUNT_ALL') . $countAll . '<br>' .
                    GetMessage('COMMENT_COUNT_UPDATE') . $countUpdate . '<br>' .
                    GetMessage('COMMENT_COUNT_ADD') . $countAdd . '<br>' .
                    GetMessage('COMMENT_COUNT_DEL') . $countDel . '<br>' .
                    '<b>' . $comment . '</b>',
                "HTML" => true,
                "TYPE" => "OK",
            );
            $msg = new \CAdminMessage($arrMessage);
            $return['HTML'] = $msg->Show();
            return json_encode($return, JSON_UNESCAPED_UNICODE);
        }
    }

    private function parseProperties($data)
    {
        $multipleProperties = [];
        $properties = [];
        foreach ($data as $propName => $propValue) {
            $propName = strtoupper($propName);

            if (empty($propValue)) continue;
            if ($propName === 'IMAGES') continue;
            if ($propName === 'DESCRIPTION') continue;

            if (is_array($propValue)) {
                $multipleProperties[$propName] = $propValue;
            } else {
                $properties[$propName] = $propValue;
                $this->checkIBProperties($propName);
            }
        }

        return $properties;
    }

    private function checkIBProperties($code, $type = 'S', $multiple = 'N')
    {
        if (!isset($this->allIBProperties)) {
            $allProperties = [];
            $iBlockProperties = \CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$this->optionsModule["iblock"]));
            while ($propFields = $iBlockProperties->GetNext()) {
                $allProperties[$propFields["CODE"]] = $propFields["NAME"];
            }
            $this->allIBProperties = $allProperties;
        }

        if (!isset($this->allIBProperties[$code])) {
            $arFields = Array(
                "NAME" => $code,
                "ACTIVE" => "Y",
                "SORT" => "100",
                "CODE" => $code,
                "PROPERTY_TYPE" => $type,
                "MULTIPLE" => $multiple,
                "IBLOCK_ID" => $this->optionsModule["iblock"]
            );
            $ibp = new \CIBlockProperty;
            $ibp->Add($arFields);
            $this->allIBProperties[$code] = $code;
        }
    }

    private function getIBElements()
    {
        $rsIbElements = \CIBlockElement::GetList(
            array(),
            array(
                'IBLOCK_ID' => $this->optionsModule["iblock"],
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

    private function getFilePropertyValue($arrUrl)
    {
        foreach ($arrUrl as $url) {
            $fileValue[] = ['VALUE' => \CFile::MakeFileArray($url), 'DESCRIPTION' => $url];
        }
        return $fileValue;
    }

    private function updateFilePropertyValues($idElement, $arrIBFile, $arrFeedFile, $propertyCode)
    {
        $arrDeleteFile = [];
        $arrNotNeedUpdateFile = [];
        foreach ($arrIBFile['DESCRIPTION'] as $key=>$value) {
            if (!in_array($value, $arrFeedFile)) {
                $arrDeleteFile[$arrIBFile['PROPERTY_VALUE_ID'][$key]] = array('del' => 'Y', 'tmp_name' => '');
            } else {
                $arrNotNeedUpdateFile[] = $value;
            }
        }

        if (!empty($arrDeleteFile)) {
            $this->elementObject->SetPropertyValueCode($idElement, $propertyCode, $arrDeleteFile);
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

}