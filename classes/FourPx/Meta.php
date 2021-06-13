<?
namespace FourPx;

use \Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Context,
    \Bitrix\Main\Page,
    \Bitrix\Main\Data\Cache;

/*
 * Класс для переопределения
 * мета тегов title, description, keywords
 * и заголовка h1
 */
class Meta
{
    const IBLOCK_TYPE = 'seo';

    /*
     * Получение текущей страницы
     */
    public static function getCurrentUrl() {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /*
     * Получение значений мета тегов
     * @return array || false
     */
    public static function getMeta() {
        $result = false;

        $url = self::getCurrentUrl();
        $IblockId = \FourPx\Helper::getIblockIdByCode('seo');
        $cacheTime = 3600;
        $cacheId = $url;
        $cache = Cache::createInstance();
        $cacheInitDir = '/seo';
        if ($cache->initCache($cacheTime, $cacheId, $cacheInitDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {

            if (Loader::includeModule('iblock')) {
                global $CACHE_MANAGER;
                $CACHE_MANAGER->StartTagCache($cacheInitDir);
                $CACHE_MANAGER->RegisterTag('iblock_id_'.$IblockId);
                $arMeta = array();
                $rsElement = \CIBlockElement::GetList(
                    array(),
                    array(
                        'IBLOCK_ID' => $IblockId,
                        'IBLOCK_TYPE' => self::IBLOCK_TYPE,
                        'CODE' => $url,
                        'ACTIVE_DATE' => 'Y',
                        'ACTIVE' => 'Y'
                    ),
                    false,
                    false,
                    array(
                        'ID',
                        'IBLOCK_ID',
                        'NAME',
                        'PROPERTY_h1',
                        'PROPERTY_title',
                        'PROPERTY_description',
                        'PROPERTY_keywords',
                        'PREVIEW_TEXT'
                    )
                );
                if ($arElement = $rsElement->Fetch()) {
                    $arMeta['h1'] = $arElement['PROPERTY_H1_VALUE'];
                    $arMeta['title'] = $arElement['PROPERTY_TITLE_VALUE'];
                    $arMeta['description'] = $arElement['PROPERTY_DESCRIPTION_VALUE'];
                    $arMeta['keywords'] = $arElement['PROPERTY_KEYWORDS_VALUE'];
                    $arMeta['seo_text'] = $arElement['PREVIEW_TEXT'];
                } else {
                    $cache->abortDataCache();
                }
                unset($rsElement);

                $result = $arMeta;
                unset($arMeta);
                $CACHE_MANAGER->EndTagCache();
            }
            $cache->endDataCache($result);
        }

        return $result;
    }

    /*
     * Устанавливает/переопределяет мета теги
     */
    public static function setMeta() {

        global $APPLICATION;

        if ($arMeta = self::getMeta()) {
            if (! empty($arMeta['h1'])){
                $APPLICATION->SetTitle($arMeta['h1']);
            }

            if (! empty($arMeta['title'])){
                $APPLICATION->SetPageProperty('title', $arMeta['title']);
            }

            if( ! empty($arMeta['description'])){
                $APPLICATION->SetPageProperty('description', $arMeta['description']);
            }

            if (! empty($arMeta['keywords'])){
                $APPLICATION->SetPageProperty('keywords', $arMeta['keywords']);
            }
        }

        if(!$APPLICATION->GetPageProperty('og:image')){
            $APPLICATION->SetPageProperty('og:image', 'http'.($_SERVER['HTTPS']?'s':'').'://'.$_SERVER['HTTP_HOST'].'/images/logo.png');
        }

    }

    /*
     * Устанавливает теги для страниц пагинации
     */
    /*public static function setMetaPagination() {
        global $APPLICATION;

        $request = Context::getCurrent()->getRequest();
        $query = $APPLICATION->GetCurParam();

        $page = array();
        $title = '';
        $description = '';

        if (preg_match('/PAGEN_[0-9]+/', $query, $pageCode)) {

            $title = $APPLICATION->GetPageProperty('title');
            $description = $APPLICATION->GetPageProperty('description');

            $page['code'] = $pageCode[0];
            $page['num'] = $request->getQuery($page['code']);
            $page['text'] = 'Страница ' . $page['num'];

            if ($title !== '') {
                $page['title'] = $title . ' | ' . $page['text'];
                $APPLICATION->SetPageProperty('title', $page['title']);
            }

            if ($description !== '') {
                $page['description'] = $description . ' ' . $page['text'] . '.';
                $APPLICATION->SetPageProperty('description', $page['description']);
            }

            self::setMetaCanonical();
        }
    }*/

    /*
     * Устанавливает canonical для страницы
     */
    /*public static function setMetaCanonical() {
        global $APPLICATION;

        $pageCanonical = (\CMain::IsHTTPS()) ? 'https://' : 'http://';
        $pageCanonical .= $_SERVER['HTTP_HOST'];
        $pageCanonical .= $APPLICATION->GetCurPage(false);

        Page\Asset::getInstance()->addString('<link rel="canonical" href="'.$pageCanonical.'"/>', true);
    }*/
}