<?php

namespace FourPx\Sale;

use Bitrix\Main\Application as DB;

class Tools {

    /**
     * Функция возвращает число с сопроводительным словом в правильном числе
     * @param $number int
     * @param $titles array склонений array(0 => одно яблоко, 1 => два яблока, 2 => 5 яблок)
     * @return string вовзращает строку вида: 5 яблок
     */
    static public function getNumberWord($number, $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        return $number . " " . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }

    /**
     * проверяет находится ли товар в списке сравнения
     * @param $arItemId - id твоара
     * @param $compareListName - название списка сравнения
     * @param $iblockId - id инфоблока
     * @return bool
     */
    static public function checkItemCompared($arItemId, $compareListName, $iblockId)
    {
        return isset($_SESSION[$compareListName][$iblockId]['ITEMS'][$arItemId]);
    }

    /**
     * Возвращает количество элементов в списке сравнения
     * 
     * @param $compareListName - название списка сравнения
     * @param $iblockId - id инфоблока
     * @return int
     */
    static public function getCountInCompareList($compareListName, $iblockId)
    {
        return count($_SESSION[$compareListName][$iblockId]['ITEMS']);
    }

    /**
     * Метод устанавливает значения цены для элемента, используется в result_modifier компонентов каталога
     * @param $arItem - элемент каталога
     * @param $selectedOffer - заранее определенное ТП
     */
    public static function setPriceForItem(&$arItem, $selectedOffer = false)
    {
        //если товар с торговыми предложениями то свойство минимальная цена у него отсутствует
        //потому проходим по списку торговых предложений и берем параметры цены
        //у торгового предложения с минимальной ценой

        if (empty($arItem['MIN_PRICE']['VALUE'])) {

            $min_offer = self::getMinPriceInOffers($arItem['OFFERS']);

            $arItem['PRICE_OLD'] = intval($min_offer['SELECT_OFFER']['PROPERTIES']['OLD_PRICE']['VALUE']);
            $arItem['PRICE_NEW'] = intval($min_offer['DISCOUNT_VALUE']) ?: intval($min_offer['VALUE']);

            $arItem['SELECT_OFFER'] = $min_offer['SELECT_OFFER'];

            $arItem['ID_CART'] = $min_offer['ID_CART'];
            $arItem['CATALOG_QUANTITY'] = $min_offer['CATALOG_QUANTITY'];

        } else {
            $arItem['PRICE_OLD'] = intval($arItem['PROPERTIES']['OLD_PRICE']['VALUE']);
            $arItem['PRICE_NEW'] = intval($arItem['MIN_PRICE']['DISCOUNT_VALUE']) ?: intval($arItem['MIN_PRICE']['VALUE']);

            $arItem['ID_CART'] = $arItem["ID"];
        }

        if ($arItem['PRICE_OLD'] <= $arItem['PRICE_NEW']) {
            $arItem['PRICE_OLD'] = 0;
        }
    }

    /**
     * Проверяем наличие количества у торговых предложений товара
     * @param array $offers массив торговых предложений
     * @return int
     */
    public static function checkCountInOffers($offers)
    {
        $result = 0;
        if (empty($offers) || !is_array($offers)) {
            return $result;
        }

        foreach ($offers as $item) {
            if ($item['CATALOG_QUANTITY'] > 0) {
                $result = 1;
                break;
            }
        }

        return $result;
    }

    /**
     * Получаем минимальную цену на товар среди торговых предложений в наличии
     * @param array $offers массив торговых предложений
     * @return array массив цены на торговое предложение с наличием
     */
    public static function getMinPriceInOffers($offers, $selectedOffer = false)
    {

        $finded_offer = Tools::getOfferWithMinPrice($offers, $selectedOffer);

        $offer = Array(
            'VALUE' => $finded_offer['MIN_PRICE']['VALUE'],
            'DISCOUNT_VALUE' => $finded_offer['MIN_PRICE']['DISCOUNT_VALUE'],
            'ID_CART' => $finded_offer['ID'],
            'CATALOG_QUANTITY' => $finded_offer['CATALOG_QUANTITY'],
            'SELECT_OFFER' => $finded_offer
        );

        return $offer;
    }

    /**
     * Торговое предложение с минимальной ценой
     * @param array $offers массив торговых предложений 
     * @param array $selectedOffer массив торгового предложения
     * @return array массив торгового предложения
     */
    public static function getOfferWithMinPrice($offers, $selectedOffer = false)
    {
        $stock_offers = self::cleanStockOffers($offers);
        if (empty($stock_offers)) {
            $stock_offers = $offers;
        }

        $min_val = false;

        foreach ($stock_offers as $v) {

            if (!empty($selectedOffer) && $v['ID'] != $selectedOffer['ID']) {
                continue;
            }

            $offer_min = self::getMinPrice($v['MIN_PRICE']);

            if ($min_val === false || $min_val > $offer_min) {
                $min_val = $offer_min;

                $offer = $v;
            }
        }

        return $offer;
    }

    /**
     * Удаляем из списка торговых предложений те что не в наличии
     * @param array $offers массив торговых предложений
     * @return mixed array или false
     */
    public static function cleanStockOffers($offers)
    {
        if (empty($offers)) {
            return false;
        }

        foreach ($offers as $k => $item) {
            if ($item['CATALOG_QUANTITY'] < 1) {
                unset($offers[$k]);
            }
        }

        return $offers;
    }

    /**
     * Берем минимальную из массива цены
     * @param array $price_array массив цены
     * @return int минимальная цена
     */
    public static function getMinPrice($price_array)
    {
        if (empty($price_array['DISCOUNT_VALUE'])) {
            return $price_array['VALUE'];
        }
        return min(Array($price_array['VALUE'], $price_array['DISCOUNT_VALUE']));
    }

    /**
     * Проверяет наличие товара в списке избранного пользователя
     *
     * @param $itemId = id товара
     * @param $listName - название списка
     * @return bool
     */
    public static function checkItemFavorite($itemId, $listName)
    {
        return in_array($itemId, $_SESSION[$listName]['ITEMS']);
    }

    /**
     * Возвращает массив всех доступных свойств инфоблока
     *
     * @param $iblockId - ID информационного блока
     * @return array
     */
    public static function getAllIblockCodeProps($iblockId)
    {
        $ret = array();
        $props = \CIBlockProperty::GetList(array('SORT' => 'ASC'), array('IBLOCK_ID' => $iblockId));
        while ($prop = $props->Fetch()) {
            $ret[] = $prop['CODE'];
        }
        return $ret;
    }


    /**
     * Получение справочника свойств инфоблока где ключём явлется код свойства
     *
     * @param null $iblockCode
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getIBlockPropsReference($iblockCode = null)
    {
        $arRef = [];

        if (\Bitrix\Main\Loader::includeModule('iblock')) {

            if ($rsRef = DB::getConnection()
                ->query("
                        SELECT
                          `bip`.`ID`,
                          `bip`.`CODE`,
                          `bip`.`NAME`,
                          `bip`.`PROPERTY_TYPE`
                        FROM `b_iblock` `bi`
                          INNER JOIN `b_iblock_property` `bip`
                            ON `bi`.`ID` = `bip`.`IBLOCK_ID`
                        WHERE `bi`.`CODE` = '" . $iblockCode . "';
                    ")
            ) {
                while ($item = $rsRef->fetch()) {
                    $arRef[$item['CODE']] = $item;
                }
            }
        }

        return $arRef;
    }


    /**
     *  Запись в лог.
     *
     *  @param string $fileName имя файла	log/aaa.html
     *  @param variant $data значение для журналирования
     *  @param int $line номер строки	__LINE__
     *  @param string $file путь к файлу	__FILE__
     *  @param bool $clear почистить ли файл
     *  @param string $headcolor выдилить цветом запись
     */
    public static function writeToLog($fileName = 'log1723.html', $data = null, $clear = false, $headcolor = "#EEEEEE")
    {
        $calledBy = debug_backtrace()[0];

        $line = $calledBy["line"];
        $file = $calledBy["file"];

        if (class_exists("CUser") && \CUser::IsAuthorized()) {
            $user = \CUser::GetFullName()."[".\CUser::GetID()."]";
        }

        $fileName = $fileName;

        if ($clear)
            unlink($_SERVER["DOCUMENT_ROOT"]."/".$fileName);

        $f_o = fopen($_SERVER["DOCUMENT_ROOT"]."/".$fileName,"a");

        fwrite($f_o, "<div style='background-color:".$headcolor."; padding:5px; font-family:\"Tahoma\"; font-size:12px;'> <b>D</b>: ".date("Y-m-d\TH:i:s") . (isset($user) ? "&nbsp;&nbsp;&nbsp;<b>U</b>: ".$user : "") . (isset($line) ? "&nbsp;&nbsp;&nbsp;<b>L</b>: ".$line : "") . (isset($file) ? "&nbsp;&nbsp;&nbsp;<b>F</b>: ".$file : "") . "</div><pre>".print_r($data, true)."</pre>");

        fclose($f_o);
    }

    /**
     * @param null $data Переменная для вывода
     * @param bool $onlyForAdmin Выводить только для админов true/false
     */
    public static function print_r($data = null, $onlyForAdmin = true)
    {
        global $USER;

        $isAdmin = $USER->IsAdmin();

        if ($onlyForAdmin && $isAdmin || ! $onlyForAdmin) {
            $sender = debug_backtrace()[0];

            echo "<pre style='font-size: 12px'><span style='font-size: 12px; color: #AAA;'>" . $sender["file"] . " <span style='color: #666;'>[строка: " . $sender["line"] . "]</span></span><br>";
            print_r($data);
            echo "</pre>";
        }
    }

    /**
     *  Возвращает url, сохраняя старые get-параметры, добавляя новые и удаляя
     *  те, что в списке для удаления
     *
     *  @param string $currentUrl URL, который будет меняться
     *  @param array Массив ключ-значение для добавления get-параметров
     *  @param array Массив ключей для удаления get-параметров
     *
     *  return string Новый URL
     */
    public static function buildQuery($getParams, $arAddParams, $arDelParams)
    {

        if (! is_array($getParams)) {
            parse_str($getParams, $getParams);
        }

        foreach ($arAddParams as $key => $value) {
            $getParams[$key] = $value;
        }

        foreach ($arDelParams as $key) {
            unset($getParams[$key]);
        }

        if (count($getParams) > 0) {
            $result = http_build_query($getParams);
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     *  Получение ID инфоблока по его коду
     *
     *  @param array $arIblockCodes массив кодов
     */
    public static function getIblockIdByCode($arIblockCodes) {
        $arIblocks = [];

        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            if ($rsIblocks = \CIBlock::GetList([], ['CODE' => $arIblockCodes])) {
                while ($iblock = $rsIblocks->Fetch()) {
                    $arIblocks[ $iblock['CODE'] ] = $iblock['ID'];
                }
            }
        }

        return $arIblocks;
    }
}
