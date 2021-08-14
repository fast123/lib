<?php


namespace LibSite;


use Bitrix\Main\Mail\Event;
use LibSite\Iblock\IbTable;

class Mail
{
    /**
     * @param $arPost array данные из формы вида $key=>$value
     * @param $EVENT_NAME - почтовое событие
     *
     * @return false
     */
    public static function sendMail($arPost, $EVENT_NAME){
        \Bitrix\Main\Loader::includeModule('iblock');
        $ev = new Event;

        $arData = self::getData($arPost);
        if($arData){
            foreach ($arData as $code=>$data){
                $arPost[$code] = $data;
            }
        }

        if ($idEv = $ev::send(
            [
              "EVENT_NAME" => $EVENT_NAME,
              "LID" => "s1",
              "C_FIELDS" => $arPost,
            ]
        )){
            return $idEv;
        }else{
            return false;
        }
    }

    /**
     * Метод для получения доп данных из инфоблока, для каждого сайта свое
     * пример получения эмейла дилерского цента, для отправки по немк
     * @return $result array | false - доп данные в виде масива
     */
    public static function getData($arPost){
        $IBLOCK_ID = IbTable::getIblockIdByCodes($arPost['ibCode']);
        $arSelect = ['ID', 'NAME', 'IBLOCK_ID'];


        $res = \CIBlockElement::GetList(
            ['sort' => 'asc'],
            ['IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE'=>'Y', 'ID'=>$arPost['DEALER']],
            false,
            [],
            $arSelect
        );

        if($obj = $res->GetNext(true, false)) {
            $dealer = $obj;
        }

        if(!empty($dealer['PROPERTY_EMAIL_TO_VALUE'])){
            $result['EMAIL_TO'] = trim($dealer['PROPERTY_EMAIL_TO_VALUE']);
        }

        return $result;
    }
}