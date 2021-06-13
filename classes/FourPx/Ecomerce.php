<?php

namespace FourPx;

class Ecomerce {
    /*
     * Запись отображаемего автомобиля на с транице в сесию
     *
     * @param array $arDataCar - масив с описание авто следующего вида
     * id	string	Идентификатор товара (если нет – случайное число)
     * actionField string Идентификатор транзакции (если он отсутствует, то random number)
     * name	string	Название автомобиля
     * price	integer	Цена автомобиля
     * brand	string	Название бренда автомобиля
     * category	string	Название модели автомобиля
     * variant	string	Цвет автомобиля (кузова)
     *
     * Если каким-то параметрам присвоить значение невозможно (например, цвет), то вместо значения отправлять “1”, в id – случайное число.
     */
    public static function setSesionCar($arDataCar){
        global $APPLICATION;
        $arResult = $arDataCar;
        $arResult['id'] = (!empty($arDataCar['id'])) ? (string) $arDataCar['id'] : (string) rand(0, 10000);
        $arResult['actionField'] = (!empty($arDataCar['actionField'])) ? (string) $arDataCar['actionField'] : (string) rand(0, 10000);
        $arResult['name'] = (!empty($arDataCar['name'])) ? (string) $arDataCar['name'] : '1';
        $arResult['price'] = (!empty($arDataCar['price'])) ? (int) $arDataCar['price']  : 1;
        $arResult['brand'] = (!empty($arDataCar['brand'])) ? (string) $arDataCar['brand']  : '1';
        $arResult['category'] = (!empty($arDataCar['category'])) ? (string) $arDataCar['category']  : '1';
        $arResult['variant'] = (!empty($arDataCar['variant'])) ? (string) $arDataCar['variant']  : '1';
        $_SESSION['DATA_CAR'] = $arResult;
        $APPLICATION->SetPageProperty('isPageCar', true);

    }
    public static function setSesionCarList($arDataCar){
        foreach ($arDataCar as $key=>$arCar){
            $arResult['VALUE'][$key]['id'] = (!empty($arCar['id'])) ? (string) $arCar['id'] : (string) rand(0, 1000);
            $arResult['VALUE'][$key]['name'] = (!empty($arCar['name'])) ? (string) $arCar['name'] : '1';
            $arResult['VALUE'][$key]['price'] = (!empty($arCar['price'])) ? (int) $arCar['price']  : 1;
            $arResult['VALUE'][$key]['brand'] = (!empty($arCar['brand'])) ? (string) $arCar['brand']  : '1';
            $arResult['VALUE'][$key]['category'] = (!empty($arCar['category'])) ? (string) $arCar['category']  : '1';
            $arResult['VALUE'][$key]['variant'] = (!empty($arCar['variant'])) ? (string) $arCar['variant']  : '1';
        }
        if(!empty( $arResult['VALUE'])){
            $arResult['actionField'] = rand(0, 10000);
            $_SESSION['DATA_CAR_LIST']= $arResult;
        }

    }

    public static function getSesionCar(){
        return $_SESSION['DATA_CAR'];
    }

    public static function getSesionCarList(){
        return $_SESSION['DATA_CAR_LIST'];
    }

    public static function getEmptyCar(){
        $arResult['id'] = (string) rand(0, 10000);
        $arResult['actionField'] = (string) rand(0, 10000);
        $arResult['name'] = '1';
        $arResult['price'] =  1;
        $arResult['brand'] = 'Genesis';
        $arResult['category'] = '1';
        $arResult['variant'] = '1';
        return $arResult;

    }

    public static function loadPage(){
        global $APPLICATION;
        if($APPLICATION->GetPageProperty('isPageCar')){
            $arDataCar = self::getSesionCar();
            $result = '<script data-skip-moving="true">
                            window.dataLayer = window.dataLayer || [];
                            dataLayer.push({
                            "event": "detail",
                            "eventAction": "viewForm",
                                "ecommerce": {
                                    "detail": {
                                        "products": [
                                            {
                                                "id": "' . $arDataCar['id'] . '",
                                                "name": "' . $arDataCar["name"] . '",
                                                "price": "' . $arDataCar['price'] . '",
                                                "brand": "' . $arDataCar['brand'] . '",
                                                "category": "' . $arDataCar['category'] . '",
                                                "variant": "' . $arDataCar['variant'] . '",
                                            }
                                        ]
                                    }
                                }
                            });
                           
                        </script>';

        }


        return $result;
    }



}