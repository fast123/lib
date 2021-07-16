<?php

namespace Project\Sale;

/**
 * класс подготавливает массивы, для работы
 * с торговыми предложениями в публичной части сайта
 *
 * Class OffersMatrix
 * @package Progect
 */
class OffersMatrix {

    const DF_MAIN_PICTURE_PROP = 'IMAGE';
    
    protected static $cache = array();
    protected $arItem;
    protected $iblockId;
    protected $offersIblockId;
    protected $arOffers;
    protected $firstOffer;
    protected $offersData;
    protected $offersJs;
    protected $productJS;
    protected $amount_productsJS;

    /**
     * Название свойств доп. изображений
     * @var array 
     */
    public $image_prop_list = Array(
        '',
        '',
        '',
    );

    /**
     * OffersMatrix constructor.
     * @param $arItem - массив элемента каталога
     * @param $arOffers - массив торговых предложений
     */
    public function __construct($arItem, $arOffers)
    {
        \CModule::IncludeModule('iblock');
        $this->arItem = $arItem;
        $this->iblockId = $arItem['IBLOCK_ID'];
        $iblockOffers = [];
        if (isset(self::$cache['IBLOCK_OFFERS'][$this->iblockId])) {
            $iblockOffers = self::$cache['IBLOCK_OFFERS'][$this->iblockId];
        } else {
            $iblockOffers = \CIBlockPriceTools::GetOffersIBlock($this->iblockId);
            self::$cache['IBLOCK_OFFERS'][$this->iblockId] = $iblockOffers;
        }

        $this->arOffers = $arOffers;

        $this->offersIblockId = $iblockOffers['OFFERS_IBLOCK_ID'];
        if ($arOffers) {
            $this->build($arOffers);
        }
    }

    /**
     * проверяет есть ли у товара торговые предложения
     *
     * @return bool
     */
    public function hasOffer()
    {
        return count($this->arOffers) > 0;
    }

    /**
     * Проверяет не является ли данный товар товаром с единственным торговым предложением
     * @return boolean
     */
    public function hasOfferList()
    {
        $data = $this->getOffersData();
        $props_count = true;

        //если свойство одно, и уникальный вариант выбора всего один
        //то тоже считаем что товар с одним торговым предложением
        if (count($data) === 1) {
            $data_array = array_shift($data);
            $unique = array_unique($data_array);

            $props_count = count($unique) > 1;
        }

        return count($this->arOffers) > 1 && $props_count;
    }

    /**
     * Возвращает торговое предложение в зависимости от переданных параметров
     *
     * $params - параметры торгового предложения
     * @return mixed
     */
    public function getFirstOffer($params = Array())
    {
        $result = Array();

        if (empty($params)) {
            $result = $this->firstOffer;
        } else {
            foreach ($this->arOffers as $v) {
                $find = true;

                foreach ($params as $pk => $pv) {
                    $key = mb_strtoupper($pk, 'utf-8');
                    $val = mb_strtolower($v['PROPERTIES'][$key]['VALUE'], 'utf-8');
                    if (empty($val)) {
                        $find = false;
                        break;
                    }

                    $find = $find && translite($val) == $pv;
                }

                if ($find) {
                    $result = $v;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * возвращает массив предложений
     *
     * @return mixed
     */
    public function getOffersData()
    {
        return $this->offersData;
    }

    /**
     * возвращает массив предложений, которые можно купить
     *
     * @return mixed
     */
    public function getOffersDataBuy()
    {

        $offers_id = Array();
        foreach ($this->offersJs as $v) {
            if ($v['CAN_BUY']) {
                $offers_id[] = $v['ID'];
            }
        }

        $offersData = $this->offersData;

        foreach ($offersData as $koff => $offer) {
            foreach (array_keys($offer) as $k) {
                if (!in_array($k, $offers_id)) {
                    unset($offersData[$koff][$k]);
                }
            }
        }
        return $offersData;
    }

    /**
     * Возвращает свойство ТП по коду
     *
     * @param $code
     * @return array
     */
    public function getOffersPropByCode($code)
    {
        return $this->getOffersData()[$code];
    }

    /**
     * возвращает json массив предложений
     *
     * @return mixed
     */
    public function getOffersJs()
    {
        return json_encode($this->offersJs);
    }

    /**
     * возвращает json массив товара
     *
     * @return mixed
     */
    public function getProductJS()
    {
        return json_encode($this->productJS);
    }

    /**
     * возвращает json массив кол-во товаров на складе
     *
     * @return mixed
     */
    public function getAmountProductsJS()
    {
        return json_encode($this->amount_productsJS);
    }

    /**
     * вовзвращает список свойств доступных для выбора
     * вместе со значениями
     *
     * @return array
     */
    public function getFillProps()
    {
        $ret = array();
        $resultystemProps = $this->getSystemProps();
        foreach ($this->arOffers as $offer) {
            foreach ($offer['PROPERTIES'] as $prop) {
                if (!$prop['VALUE']) {
                    continue;
                }

                if (in_array($prop['CODE'], $resultystemProps)) {
                    continue;
                }

                $val = $prop['VALUE'];
                if (!isset($ret[$prop['CODE']])) {
                    $prop['VALUE'] = array();
                    $ret[$prop['CODE']] = $prop;
                }

                if (!in_array($val, $ret[$prop['CODE']]['VALUE'])) {
                    $ret[$prop['CODE']]['VALUE'][] = $val;
                }
            }
        }

        foreach ($ret as $propCode => $prop) {
            if (count($prop['VALUE']) <= 1) {
                unset($ret[$propCode]);
            }
        }

        return $ret;
    }

    /**
     * возвращает количество заполненных
     * свойств у предложений
     *
     * @return int
     */
    public function getCountFillProps()
    {
        return count($this->getFillProps());
    }

    /**
     * Получение дополнительных изображений
     * @param array $curent текущиее торговое предложение 
     * @param array $result товар 
     */
    public function getAddImages($curent, $result)
    {
        foreach ($this->image_prop_list as $v) {
            $curent['PROPERTIES'][$v]['VALUE'] = $result['PROPERTIES'][$v]['VALUE'];
        }
        return $curent;
    }

    /**
     * Получение дополнительных изображений для JS
     * @param array $curent текущиее торговое предложение 
     * @param array $result товар 
     */
    public function getAddImagesJs($curent, $result)
    {
        foreach ($this->image_prop_list as $v) {
            if (!empty($result['PROPERTIES'][$v]['VALUE'])) {
                $curent['PROPERTIES']['ADD_IMAGE'][$v]['VALUE'] = $result['PROPERTIES'][$v]['VALUE'];
            }
        }

        return $curent['PROPERTIES']['ADD_IMAGE'];
    }

    /**
     * Проверка если доп. изобрадения у текущего предложения
     * @param array $curent текущиее торговое предложение
     * return false если изображения для торгового предложения существуют
     */
    function existImages($curent)
    {
        $k = true;
        foreach ($this->image_prop_list as $v) {

            if (!empty($curent['PROPERTIES'][$v]['VALUE'])) {
                $k = false;
                break;
            }
        }
        return $k;
    }

    /**
     * Получение кол-ва остатков товара на складах
     * @param type $id int
     * return string
     */
    public function getAmount($id)
    {
        $amount = $this->getAmountIntegerOnly($id);

        //$amount = '';
        // $store_id = array(2, 13, 14, 35, 37);
        // foreach ($store_id as $v) {
        //     $rsStore = \CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' => $id, 'STORE_ID' => $v), false, false, array('AMOUNT'));
        //     if ($arStore = $rsStore->Fetch()) {
        //         $amount += $arStore['AMOUNT'];
        //     }
        // }
        if ($amount <= 10 && $amount != 0) {
            $amount = $amount;
        } elseif ($amount > 10) {
            $amount = '>10';
        }

        return $amount;
    }

    /**
     * (Копия предыдущего метода, так как в шаблоне использется численное сравнение) Получение кол-ва остатков товара на складах для каталога
     * @param type $id int
     * return string
     */
    public function getAmountIntegerOnly($id)
    {
        $amount = '';
        $store_id = array(2, 13, 14, 35, 37);
        foreach ($store_id as $v) {
            $rsStore = \CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' => $id, 'STORE_ID' => $v), false, false, array('AMOUNT'));
            if ($arStore = $rsStore->Fetch()) {
                $amount += $arStore['AMOUNT'];
            }
        }
        if ($amount <= 10 && $amount != 0) {
            $amount = $amount;
        } 

        return $amount;
    }

    /**
     * обрабатывает торговые предлоежния
     *
     * @param $offers
     */
    protected function build($offers)
    {

        $resultystemProps = $this->getSystemProps();
        $itemProperties = $this->arItem['PROPERTIES'];
        $propertyGroup = $this->getPropertyGroups();
        $propertyGroupNames = $this->getPropertyGroupNames();

        $offersData = array();
        $baseOffersData = array();
        $propertyVariants = array();
        $offersList = array();

        $firstOffer = Tools::getOfferWithMinPrice($offers);

        $listOffers = $offers;

        foreach ($offers as $offer) {
            foreach ($offer['PROPERTIES'] as $k => $v) {
                $v['VALUE'] = trim($v['VALUE']);
                if (!in_array($v['CODE'], $resultystemProps) && !empty($v['VALUE']) && $itemProperties[$k]['VALUE'] !== $v['VALUE']) {
                    //собираем свойства торговых предложений
                    $offersData[$v['CODE']][$offer['ID']] = $v['VALUE'];
                    $group = getPropGroup($v['CODE'], $propertyGroup);
                    $baseOffersData[$offer['ID']][$v['CODE']] = Array(
                        'GROUP' => $group,
                        'GROUP_NAME' => $propertyGroupNames[$group],
                        'NAME' => $v['NAME'],
                        'VALUE' => $v['VALUE']
                    );
                    $propertyVariants[$v['CODE']][$v['VALUE']] = $v['VALUE'];
                    $offersList[$offer['ID']] = $offer['ID'];
                }
            }
            $amount_productsJS[$offer['ID']] = Array(
                'ID' => $offer['ID'],
                'AMOUNT' => $this->getAmount($offer['ID']));
        }
        $this->amount_productsJS = $amount_productsJS;
        if (!$firstOffer) {
            $firstOffer = array_shift($listOffers);
        }

// Подготовка массива товара для сброса параметров через js
        $firstOffer_js = $firstOffer;
        $product = $this->arItem;
        $changes_js = $this->existImages($product);
        $imges_js = Array();
        if ($changes_js) {
            $imges_js = $this->getAddImagesJs($product, $firstOffer_js);
        } else {
            foreach ($this->image_prop_list as $v) {
                if (!empty($product['PROPERTIES'][$v]['VALUE'])) {
                    $imges_js[$v]['VALUE'] = $product['PROPERTIES'][$v]['VALUE'];
                }
            }
        }

        if (empty($product['PROPERTIES'][self::DF_MAIN_PICTURE_PROP]['VALUE'])) {
            $product['PROPERTIES'][self::DF_MAIN_PICTURE_PROP]['VALUE'] = $firstOffer_js['PROPERTIES'][self::DF_MAIN_PICTURE_PROP]['VALUE'];
        }
        $productJS[$product['ID']] = Array(
            'ID' => $product['ID'],
            'NAME' => html_entity_decode($product['NAME']),
            'MAIN_IMAGE' => $product['PROPERTIES'][self::DF_MAIN_PICTURE_PROP]['VALUE'],
            'ADD_IMAGE' => $imges_js,
            'PRICE_OLD' => $product['MIN_PRICE']['VALUE'],
            'PRICE_NEW' => round($product['MIN_PRICE']['DISCOUNT_VALUE'] ?: $product['MIN_PRICE']['VALUE']),
            'PROPERTY' => $baseOffersData[$product['ID']],
            'CAN_BUY' => intval($firstOffer_js['CATALOG_QUANTITY']) > 0,
            'ARTICLE' => $product['PROPERTIES']['CML2_ARTICLE']['VALUE']
        );

        $old_price_js = $product['PROPERTIES']['OLD_PRICE']['VALUE'];

        if (!empty($old_price_js) && intval($old_price_js) > intval($product['MIN_PRICE']['VALUE'])) {
            $firstOfferJS[$offer['ID']]['PRICE_OLD'] = $old_price_js;
        }
        $this->productJS = $productJS;

        //убираем из списка выбора свойства которые совпадают
        //у торговых предложений
        foreach ($propertyVariants as $k => $v) {
            if (count($v) < 2) {
                unset($offersData[$k]);
            }
        }

        //если торговое предложение не заполнено и его нельзя выбрать
        //добавляем псевдо-свойство - имя торгового предложения
        if (count($listOffers) < count($offers) || empty($offersData)) {
            $addedList = Array();

            foreach ($offers as $v) {
                $addedList[$v['ID']] = $v['NAME'];
            }

            if (!empty($offersData) && is_array($offersData)) {
                $offersData = array_merge(Array('NAME' => $addedList), $offersData);
            } else {
                $offersData = Array('NAME' => $addedList);
            }
        }

        //формируем свойства торгового предложения для того чтобы выводить их
        //на страницу в зависимости от выбранного пользователем торгового предложения
        $offersJs = Array();


//Список свойств картинок на вывод
        foreach ($offers as $offer) {
            $changes = $this->existImages($offer);
            $imges = Array();
            if ($changes) {
                $imges = $this->getAddImagesJs($offer, $this->arItem);
            } else {
                foreach ($this->image_prop_list as $v) {
                    if (!empty($offer['PROPERTIES'][$v]['VALUE'])) {
                        $offer['PROPERTIES']['ADD_IMAGE'][$v]['VALUE'] = $offer['PROPERTIES'][$v]['VALUE'];
                    }
                }
                $imges = $offer['PROPERTIES']['ADD_IMAGE'];
            }
            $offersJs[$offer['ID']] = Array(
                'ID' => $offer['ID'],
                'NAME' => html_entity_decode($offer['NAME']),
                'MAIN_IMAGE' => $offer['PROPERTIES'][self::DF_MAIN_PICTURE_PROP]['VALUE'],
                'ADD_IMAGE' => $imges,
                'PRICE_OLD' => $offer['MIN_PRICE']['VALUE'],
                'PRICE_NEW' => round($offer['MIN_PRICE']['DISCOUNT_VALUE'] ?: $offer['MIN_PRICE']['VALUE']),
                'PRICE_RRC' => $offer['PRICES']['01РОЗНИЦА']['VALUE'],
                'PROPERTY' => $baseOffersData[$offer['ID']],
                'CAN_BUY' => intval($offer['CATALOG_QUANTITY']) > 0,
                'ARTICLE' => $offer['PROPERTIES']['CML2_ARTICLE']['VALUE']
            );

            $old_price = $offer['PROPERTIES']['OLD_PRICE']['VALUE'];

            if (!empty($old_price) && intval($old_price) > intval($offer['MIN_PRICE']['VALUE'])) {
                $offersJs[$offer['ID']]['PRICE_OLD'] = $old_price;
            }
        }

        $this->firstOffer = $firstOffer;
        $this->offersData = $offersData;
        $this->offersJs = $offersJs;
    }

    /**
     * Вовзвращает массив кодов системных свойств
     *
     * @return array|mixed
     */
    protected function getSystemProps()
    {
        $cacheKey = 'SYSTEM_PROPS';

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $ar1 = Tools::getAllSystemProps($this->iblockId);
        $ar2 = Tools::getAllSystemProps($this->offersIblockId);

        $ar1 = is_array($ar1) ? $ar1 : array();
        $ar2 = is_array($ar2) ? $ar2 : array();

        return self::$cache[$cacheKey] = array_merge($ar1, $ar2);
    }

    /**
     * Возвращает группы свойств
     *
     * @return array
     */
    protected function getPropertyGroups()
    {
        $cacheKey = "PROPERTY_GROUPS";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        return self::$cache[$cacheKey] = Tools::getPropertyGroups();
    }

    /**
     * Возвращает названия групп свойств
     *
     * @return array
     */
    protected function getPropertyGroupNames()
    {
        $cacheKey = "GROUP_NAMES";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        return self::$cache[$cacheKey] = Tools::getPropertyGroupNames();
    }

}
