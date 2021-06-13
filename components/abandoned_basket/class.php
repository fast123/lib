<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CVeloAbandonedBasket extends CBitrixComponent
{

	public function onPrepareComponentParams($arParams)
	{
        $arParams['IS_NEW_SESSION'] = false;

	    $componentName = $this->getName();

	    if ($_SESSION[ $componentName ]['WAS_SHOWN'] != 'Y'
            && $_SESSION['BX_SESSION_COUNTER'] < 3
        ) {
            $_SESSION[ $componentName ]['WAS_SHOWN'] = 'Y';
            $arParams['IS_NEW_SESSION'] = true;
        }

		return $arParams;
	}

	public function executeComponent()
	{
        global $USER;

	    if (! $USER->IsAuthorized() && $this->arParams['IS_NEW_SESSION']) {
            # пользователь зашёл заново через какое-то время
            # значит проверяем есть ли в корзине покупке и если есть, то
            # выводим сообщение


            $fUserId = \Bitrix\Sale\Fuser::getId();

            $arBasketItems = \Bitrix\Sale\Basket::getList(
                [
                    'filter' => ['FUSER_ID' => $fUserId, 'ORDER_ID' => false],
                    'select' => ['ID', 'PRODUCT_ID', 'DATE_INSERT', 'QUANTITY', 'PRICE'],
                ]
            )->fetchAll();


            if (count($arBasketItems) > 0) {

                $arProducts = [];
                $arProductsIds = [];
                foreach ($arBasketItems as $item) {
                    $arProductsIds[ $item['PRODUCT_ID'] ] = $item['PRODUCT_ID'];
                }

                if (\Bitrix\Main\Loader::includeModule('iblock')) {

                    if ($rsProducts = \CIBlockElement::GetList(
                            [],
                            [
                                'ID' => $arProductsIds,
                            ],
                            false,
                            false,
                            ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PROPERTY_OSNOVNOE_IZOBRAZHENIE']
                    )) {

                        while ($product = $rsProducts->GetNextElement()) {
                            $product = $product->GetFields();
                            $arProducts[ $product['ID'] ] = $product;
                        }

                    }

                }

                $this->arResult['BASKET'] = $arBasketItems;
                $this->arResult['PRODUCTS'] = $arProducts;

                $this->includeComponentTemplate();
            }

        }

	}

}