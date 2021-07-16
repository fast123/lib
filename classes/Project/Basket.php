<?php

namespace Project;

class Basket {
    const DF_SKU_IBLOCK_ID = '10';
    const SKU_PROPERTY_ID = 'CML2_LINK';

    /**
     * Получаем список торговых предложений
     * @param int $item_id идентификатор товара
     * @return array
     */
    protected function getItemOffers($item_id)
    {
        if (!\CModule::IncludeModule('iblock')) {
            return;
        }

        $filter = Array(
            'IBLOCK_ID' => self::DF_SKU_IBLOCK_ID,
            'PROPERTY_' . self::SKU_PROPERTY_ID => $item_id,
            'ACTIVE' => 'Y',
            'CATALOG_AVAILABLE' => 'Y'
        );

        $select = Array(
            'ID', 'DATE_CREATE', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'CODE',
            'IBLOCK_TYPE_ID', 'DETAIL_PAGE_URL', 'ACTIVE', 'CATALOG_QUANTITY',
            'PROPERTY_*'
        );

        $res = \CIBlockElement::GetList(Array('ID' => 'ASC'), $filter, false, false, $select);

        $offers = Array();

        while ($object = $res->GetNextElement()) {
            $element = $object->GetFields();
            $element['PROPERTIES'] = $object->GetProperties();

            $old_price = intval($element['PROPERTIES']['OLD_PRICE']['VALUE']);

            if (!empty($old_price)) {
                $element['FULL_PRICE'] = $old_price;
            }

            $offers[$element['ID']] = $element;
        }

        return $offers;
    }

    /**
     * Получаем дополнительные свойства товара
     * @param int $item_id
     * @param int $iblock_id
     * @return array
     */
    protected function getItemData($item_id, $iblock_id = DF_CATALOG_IBLOCK_ID)
    {
        //получаем дополнительные свойства товара
        $filter = Array(
            'IBLOCK_ID' => $iblock_id,
            'ID' => $item_id
        );

        $select = Array(
            "IBLOCK_ID", 'IBLOCK_SECTION_ID', 'CATALOG_QUANTITY',
            "PROPERTY_CML2_ARTICLE", "PROPERTY_OLD_PRICE"
        );

        $res = \CIBlockElement::GetList(Array('ID' => 'ASC'), $filter, false, false, $select);

        $element = Array();

        while ($object = $res->GetNext()) {
            $element = $object;
        }

        return $element;
    }

    /**
     * Получаем ID товара
     * @param array $item массив данных товара
     * @return int
     */
    protected function getItemID($item)
    {
        if ($this->checkSKU($item)) {
            $sku = \CCatalogSku::GetProductInfo($item['PRODUCT_ID']);
            $item_id = $sku['ID'];
        } else {
            $item_id = $item['PRODUCT_ID'];
        }

        return $item_id;
    }

    /**
     * Проверяем является ли товар торговым предложением
     * @param array $item массив данных товара
     * @return boolean
     */
    protected function checkSKU($item)
    {
        return $item['IBLOCK_ID'] == self::DF_SKU_IBLOCK_ID;
    }

    /**
     * Количество
     * @param array $item
     * @return int
     */
    public function getItemQuantity($item)
    {
        \CModule::IncludeModule('catalog');

        $arFilter = Array("PRODUCT_ID" => $item['PRODUCT_ID']);
        $arSelectFields = Array("ID", "PRODUCT_AMOUNT");

        $res = \CCatalogStore::GetList(Array(), $arFilter, false, false, $arSelectFields);

        $sum = 0;
        while ($arRes = $res->GetNext()) {
            $sum += intval($arRes['PRODUCT_AMOUNT']);
        }

        return $sum;
    }

    /**
     * Получаем дополинительные данные товара
     * @param array $item данные товара
     * @return array полные данные товара
     */
    public function getBasketItem($item)
    {
        $cart_id = $item['ID'];
        $item_id = $this->getItemID($item);
        $added_info = $this->getItemData($item_id);

        unset($added_info['IBLOCK_ID']);

        $full_item = array_merge($item, $added_info);

        if ($this->checkSKU($item)) {
            $full_item['OFFERS'] = $this->getItemOffers($item_id);
            $full_item['FULL_PRICE'] = intval($full_item['OFFERS'][$item['PRODUCT_ID']]['FULL_PRICE']);
        } else {
            $full_item['FULL_PRICE'] = intval($full_item['PROPERTY_OLD_PRICE_VALUE']);
        }

        if (!empty($full_item['FULL_PRICE']) && intval($full_item['FULL_PRICE']) > intval($full_item['PRICE'])) {
            $full_item['DISCOUNT_PRICE'] = $full_item['PRICE'];
            $full_item['DICOUNT_DATA']['ACTIVE_EXPIRE'] = '1 день';
        }
        
        $full_item['ELEMENT_QUANTITY'] = $this->getItemQuantity($full_item);

        $full_item['IS_SKU'] = $this->checkSKU($item);
        $full_item['ID'] = $cart_id;

        return $full_item;
    }

}
