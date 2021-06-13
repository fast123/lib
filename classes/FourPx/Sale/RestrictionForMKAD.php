<?php

namespace FourPx\Sale;

use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\PersonTypeTable;
use Bitrix\Sale\ShipmentCollection;
#use Bitrix\Sale\Order;

/*
Проверка адреса в пределах мкад для доставки
*/
class RestrictionForMKAD extends Bitrix\Sale\Delivery\Restrictions\Base
{

    public static function check($addrPropValue, array $params, $deliveryId = 0)
    {
        if (intval($deliveryId) <= 0) {
            return true;
        }
        if($addrPropValue['LOCATION'] == 'Москва'){
            if(empty($addrPropValue['ADDRESS'])){
                $str_address = $addrPropValue['LOCATION'];
            }
            else{
                $str_address = $addrPropValue['ADDRESS'];
            }
            $httpClient = new \Bitrix\Main\Web\HttpClient();
            $httpClient->get('https://geocode-maps.yandex.ru/1.x/?apikey=45f4f99f-1814-495a-8439-a655a159f62a&format=json&geocode=' . $str_address);

            $response = json_decode($httpClient->getResult(), true)['response'];
            foreach ($response['GeoObjectCollection']['featureMember'] as $item) {
                if($item['GeoObject']['metaDataProperty']['GeocoderMetaData']['precision'] == 'exact'){
                    $ar_components_ardderss = $item['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Components'];
                    foreach ($ar_components_ardderss as $components){
                        if($components['kind'] == 'locality'){
                            $ar_city[] = $components['name'];
                        }
                    }
                }
            }

            if(empty($ar_city)){
                return true;
            }

            if(reset($ar_city) != 'Москва') {
                return false;
            }
        }

        //если в LOCATON выброн город из подмосковъя
        if($addrPropValue['LOCATION_AREA'] == 'Москва'){
            return false;
        }

        return false;
    }

    public static function extractParams(Bitrix\Sale\Shipment $shipment)
    {
        $collection = $shipment->getCollection();
        $order = $collection->getOrder();
        $propertyCollection = $order->getPropertyCollection();
        if ($propertyCollection->getAddress()) {
           $addrPropValue['ADDRESS']  = $propertyCollection->getAddress()->getValue();
        }
        $str_location = $propertyCollection->getDeliveryLocation()->getViewHtml();
        $ar_location = explode(", ", $str_location);
        $addrPropValue['LOCATION'] = end($ar_location);
        $addrPropValue['LOCATION_AREA'] = $ar_location[count($ar_location)-2];
        return $addrPropValue;
    }

    public static function getClassTitle()
    {
        return 'В пределах МКАД';
    }

    public static function getClassDescription()
    {
        return 'В пределах МКАД';
    }

    public static function getParamsStructure($deliveryId = 0)
    {
        return array();
    }

    public static function getSeverity($mode)
    {
        return \Bitrix\Sale\Delivery\Restrictions\Manager::SEVERITY_STRICT;
    }
}