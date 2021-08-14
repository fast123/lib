<?php


namespace LibSite\Hl;

use Bitrix\Main\Entity;


class Event
{
    public static function ColorReferenceBeforeUpdate(Entity\Event $event)
    {
        $id = $event->getParameter("id");
        //id обновляемого элемента
        $id = $id["ID"];

        $entity = $event->getEntity();
        $entityDataClass = $entity->GetDataClass();
        // тип события. вернет ColorsOnBeforeUpdate
        $eventType = $event->getEventType();
        // получаем массив полей хайлоад блока
        $arFields = $event->getParameter("fields");

        $result = new \Bitrix\Main\Entity\EventResult();


        if (empty($arFields['UF_LINK'])) {
            //только вывод ошибки, поля все равно будут заполнены
            $arErrors = Array();
            $arErrors[] = new \Bitrix\Main\Entity\FieldError($entity->getField("UF_LINK"), "Ошибка в поле UF_LINK. Поле не должно быть пустым!");
            $result->setErrors($arErrors);

            //для отмены нужно получить елемент и перезаписать данные
            //пример модификация данных
            /*if (empty($arFields['UF_LINK'])) {
                $arFields['UF_LINK'] = 'lol';
                $result->modifyFields($arFields);
            }*/
        }

        return $result;
    }

    function OnAfterUpdate(\Bitrix\Main\Entity\Event $event) {
        $id = $event->getParameter("id");
        //id обновляемого элемента
        $id = $id["ID"];

        $entity = $event->getEntity();
        $entityDataClass = $entity->GetDataClass();
        // тип события. вернет ColorsOnAfterUpdate
        $eventType = $event->getEventType();
        // получаем массив полей хайлоад блока
        $arFields = $event->getParameter("fields");
    }

    function OnBeforeAdd(\Bitrix\Main\Entity\Event $event) {
        //id добавляемого элемента
        $id = $event->getParameter("id");

        $entity = $event->getEntity();
        $entityDataClass = $entity->GetDataClass();
        // тип события. вернет ColorsOnAfterAdd
        $eventType = $event->getEventType();
        // получаем массив полей хайлоад блока
        $arFields = $event->getParameter("fields");
    }

    function OnAfterAdd(\Bitrix\Main\Entity\Event $event) {
        //id добавляемого элемента
        $id = $event->getParameter("id");

        $entity = $event->getEntity();
        $entityDataClass = $entity->GetDataClass();
        // тип события. вернет ColorsOnAfterAdd
        $eventType = $event->getEventType();
        // получаем массив полей хайлоад блока
        $arFields = $event->getParameter("fields");
    }

    function OnBeforeDelete(\Bitrix\Main\Entity\Event $event) {
        // поля в этом событии недоступны, только id
        $id = $event->getParameter("id");
        //id удаляемого элемента
        $id = $id["ID"];

        $entity = $event->getEntity();
        $entityDataClass = $entity->GetDataClass();
        // тип события. вернет ColorsOnBeforeDelete
        $eventType = $event->getEventType();

        $result = new \Bitrix\Main\Entity\EventResult();
        if ($id <= 20) {
            $arErrors = Array();
            $arErrors[] = new \Bitrix\Main\Entity\EntityError("Ошибка! Нельзя удалять первые 20 элементов!");
            $result->setErrors($arErrors);
        }

        return $result;
    }

    function OnAfterDelete(\Bitrix\Main\Entity\Event $event) {
        $id = $event->getParameter("id");
        //id удаляемого элемента
        $id = $id["ID"];

        $entity = $event->getEntity();
        $entityDataClass = $entity->GetDataClass();
        // тип события. вернет ColorsOnAfterAdd
        $eventType = $event->getEventType();
        if ($id > 30 && $id < 1000) {
            // ваша логика....
        }
    }
}