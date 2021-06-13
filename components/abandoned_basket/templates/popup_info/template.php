<?if (! defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?
    use \Bitrix\Main\Page\Asset;

#    Asset::getInstance()->addString('<script defer src="https://api-maps.yandex.ru/2.1/?lang=ru_RU"></script>');
#    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/jquery.cookie.js');
?>

<div class="abandoned-basket">
    <div class="abandoned-basket__inner">
        <div class="abandoned-basket__close">&times;</div>
        <div class="abandoned-basket__title">
            <h3>Ваша корзина очень рада, что Вы вернулись!<br>
                Самое время приобрести товары, которые Вы оставили в ней!</h3>
        </div>
        <div class="abandoned-basket__items">
            <?foreach ($arResult['BASKET'] as $basketItem):?>
                <?$product = $arResult['PRODUCTS'][ $basketItem['PRODUCT_ID'] ]?>

                <a href="<?= $product['DETAIL_PAGE_URL']?>" title="<?= $product['NAME']?>" target="_blank">
                    <div class="abandoned-basket__item">
                        <div class="abandoned-basket__item-photo">
                            <img class="abandoned-basket__item-photo-img" src="<?= $product['PROPERTY_OSNOVNOE_IZOBRAZHENIE_VALUE']?>">
                        </div>
                        <div class="abandoned-basket__item-name">
                            <?= $product['NAME']?>
                        </div>
                        <div class="abandoned-basket__item-price">
                            <?
                                $price = number_format($basketItem['PRICE'], 2, '.', ' ');
                            ?>
                            <?= $price?> &#8381;
                        </div>
                    </div>
                </a>
            <?endforeach?>
        </div>
        <div class="abandoned-basket__buttons">
            <a href="/personal/order/make/">
                <div class="vel-button vel-button_red vel-button_skewed" role="button">
                    <div class="vel-button__inner">
                        Оформить заказ
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>


