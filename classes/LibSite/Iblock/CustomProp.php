<?php


namespace LibSite\Iblock;

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Iblock;


class CustomProp
{
    /**
     * Метод возвращает массив описания собственного типа свойств
     * @return array
     */
    public function getUserTypeDescription()
    {
        return array(
            'USER_TYPE_ID' => 'user_shmt', //Уникальный идентификатор типа свойств
            'USER_TYPE' => 'TIMESHEET',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Кстомное свойство',
            'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
            'ConvertToDB' => [__CLASS__, 'сonvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'сonvertFromDB'],
            'GetPropertyFieldHtml' => [__CLASS__, 'getPropertyFieldHtml'],
        );
    }

    /**
     * Конвертация данных перед сохранением в БД
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    public static function сonvertToDB($arProperty, $value)
    {
        if ($value['VALUE']['TIME_FROM'] != '' && $value['VALUE']['TIME_TO']!='')
        {
            try {
                $value['VALUE'] = base64_encode(serialize($value['VALUE']));
            } catch(Bitrix\Main\ObjectException $exception) {
                echo $exception->getMessage();
            }
        } else {
            $value['VALUE'] = '';
        }

        return $value;
    }

    /**
     * Конвертируем данные при извлечении из БД
     * @param $arProperty
     * @param $value
     * @param string $format
     * @return mixed
     */
    public static function сonvertFromDB($arProperty, $value, $format = '')
    {
        if ($value['VALUE'] != '')
        {
            try {
                $value['VALUE'] = base64_decode($value['VALUE']);
            } catch(Bitrix\Main\ObjectException $exception) {
                echo $exception->getMessage();
            }
        }

        return $value;
    }

    /**
     * Представление формы редактирования значения
     * @param $arProperty
     * @param $value
     * @param $arHtmlControl
     */
    public static function getPropertyFieldHtml($arProperty, $value, $arHtmlControl)
    {
        $weekDays = [
            'mon' => 'Понедельник',
            'tue' => 'Вторник',
            'wed' => 'Среда',
            'thu' => 'Четверг',
            'fri' => 'Пятница',
            'sat' => 'Суббота',
            'sun' => 'Воскресенье',
        ];

        $itemId = 'row_' . substr(md5($arHtmlControl['VALUE']), 0, 10); //ID для js
        $fieldName =  htmlspecialcharsbx($arHtmlControl['VALUE']);
        //htmlspecialcharsback нужен для того, чтобы избавиться от многобайтовых символов из-за которых не работает unserialize()
        $arValue = unserialize(htmlspecialcharsback($value['VALUE']), [stdClass::class]);

        $select = '<select class="week_day" name="'. $fieldName .'[WEEK_DAY]">';
        foreach ($weekDays as $key => $day){
            if($arValue['WEEK_DAY'] == $key){
                $select .= '<option value="'. $key .'" selected="selected">'. $day .'</option>';
            } else {
                $select .= '<option value="'. $key .'">'. $day .'</option>';
            }

        }
        $select .= '</select>';

        $html = '<div class="property_row" id="'. $itemId .'">';

        $html .= '<div class="reception_time">';
        $html .= $select;
        $timeFrom = ($arValue['TIME_FROM']) ? $arValue['TIME_FROM'] : '';
        $timeTo = ($arValue['TIME_TO']) ? $arValue['TIME_TO'] : '';

        $html .='&nbsp;время приёма: с&nbsp;<input type="time" name="'. $fieldName .'[TIME_FROM]" value="'. $timeFrom . '">';
        $html .='&nbsp;по&nbsp;<input type="time" name="'. $fieldName .'[TIME_TO]" value="'. $timeTo .'">';
        if($timeFrom!='' && $timeTo!=''){
            $html .= '&nbsp;&nbsp;<input type="button" style="height: auto;" value="x" title="Удалить" onclick="document.getElementById(\''. $itemId .'\').parentNode.parentNode.remove()" />';
        }
        $html .= '</div>';

        $html .= '</div><br/>';

        return $html;
    }
}