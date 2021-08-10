<?php


namespace LibSite\Iblock;


class IbTable
{
    /**
     * проверка на существование инфоблока
     *
     * @param $ibCode символьный код инфоблока
     * @return bool
     */
    public static function isIblockExists($ibCode)
    {
        Loader::includeModule('iblock');

        if (empty($ibCode)) {
            return false;
        }

        $rsIblock = \Bitrix\Iblock\ElementTable::getList(
            [
                'filter' => [
                    'CODE' => $ibCode
                ],
                'limit' => 1
            ]
        );

        if ($rsIblock->getSelectedRowsCount() === 1) {
            return true;
        }

        return false;
    }

    /**
     * Создание инфоблока
     *
     * @param $ibCode string символьный код инфоблока
     * @param $arProps array свойства инфоблока формат $propCode=>$propType
     *
     * Поддерживаемые форматы $propType: N, S, F  и т.д
     * @return string[]
     */
    public static function createIblock(string $ibCode, $arProps)
    {
        $result = [
            'status' => 'success',
        ];

        Loader::includeModule('iblock');

        # создение типа инфоблока
        $ibType = new \CIBlockType;
        $ibTypeId = $ibType->Add(
            [
                'ID' => $ibCode,
                'SECTIONS' => 'Y',
                'IN_RSS' => 'N',
                'SORT' => '100',
                'LANG' => [
                    'ru' => [
                        'NAME' => $ibCode,
                        'SECTION_NAME' => 'Раздел',
                        'ELEMENT_NAME' => 'Элемент'
                    ],
                    'en' => [
                        'NAME' => $ibCode,
                        'SECTION_NAME' => 'Раздел',
                        'ELEMENT_NAME' => 'Элемент'
                    ]
                ]
            ]
        );

        if (!$ibTypeId) {
            $result['status'] = 'error';
            $result['message'] = $ibType->LAST_ERROR;

            return $result;
        }


        # создание инфоблока
        $ib = new \CIBlock;
        $ibId = $ib->Add(
            [
                'ACTIVE' => 'Y',
                'NAME' => $ibCode,
                'CODE' => $ibCode,
                'API_CODE' => $ibCode,
                'LIST_PAGE_URL' => '/catalog/',
                'DETAIL_PAGE_URL' => '/catalog/#ELEMENT_CODE#/',
                'IBLOCK_TYPE_ID' => $ibCode,
                'XML_ID' => $ibCode,
                'SITE_ID' => ['s1'],
                'SORT' => 100,
                'GROUP_ID' => ['2' => 'R']
            ]
        );

        if (!$ibId) {
            $result['status'] = 'error';
            $result['message'] = $ib->LAST_ERROR;

            return $result;
        }


        # создание свойств
        $ibp = new \CIBlockProperty;
        foreach ($arProps as $propCode => $propType) {
            $newProp = [
                'NAME' => $propCode,
                'ACTIVE' => 'Y',
                'SORT' => '100',
                'CODE' => $propCode,
                'IBLOCK_ID' => $ibId,
                'MULTIPLE' => 'N',
                'PROPERTY_TYPE' => $propType
            ];

            /*if ($propType === 'number') {
                $newProp['PROPERTY_TYPE'] = 'N';
            } elseif ($propType === 'text') {
                $newProp['PROPERTY_TYPE'] = 'S';
                $newProp['USER_TYPE'] = 'HTML';
            }*/

            $ibpId = $ibp->Add($newProp);

            if (!$ibpId) {
                $result['status'] = 'error';
                $result['message'] = $ibp->LAST_ERROR;
            }
        }

        return $result;
    }

    /**
     *  Получение ID инфоблока по его коду
     *
     *  @param array $arIblockCodes код или массив кодов
     */
    public static function getIblockIdByCodes($iblockCodes) {
        $result = array();

        if (Loader::includeModule('iblock')) {
            $rsIblocks = IblockTable::getList(
                array(
                    'filter' => array(
                        'CODE' => $iblockCodes
                    ),
                    'select' => array(
                        'ID',
                        'CODE'
                    )
                )
            );

            while ($iblock = $rsIblocks->fetch()) {
                $result[ $iblock['CODE'] ] = $iblock['ID'];
            }

            if (! is_array( $iblockCodes ) && is_array( $result )) {
                $result = array_pop($result);
            }
        }

        return $result;
    }
}