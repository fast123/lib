<?php

namespace FourPx;
use \Bitrix\Main\Application,
    \Bitrix\Main\Web\Cookie;

class Utm {

    /*
     *  получение utm-меток из GET-параметров
     */
    public static function getUTMFromQuery()
    {
        $request = Application::getInstance()->getContext()->getRequest();

        $getParams = $request->getQueryList()->toArray();

        $arUtm = array('utm_source' => '', 'utm_medium' => '', 'utm_campaign' => '', 'utm_content' => '', 'utm_term' => '');
        $arUtm = array_intersect_key($arUtm, $getParams);

        foreach (array_keys($arUtm) as $utmName) {
            $arUtm[ $utmName ] = $getParams[ $utmName ];
        }

        return $arUtm;
    }

    /*
     * сохранение utm-меток в cookie
     */

    public static function saveUTMToCookie()
    {
        $result = false;

        if ($arUtm = self::getUTMFromQuery()) {
            $cookie = new Cookie('utm', json_encode($arUtm));
            $cookie->setDomain( $_SERVER['HTTP_HOST'] );

            $response = Application::getInstance()->getContext()->getResponse();
            $response->addCookie( $cookie );

            $result = true;
        }

        return $result;
    }

    /*
     * получение UTM-меток из GET-параметров или из cookies
     */
    public static function getUTM()
    {
        $result = array();

        if ($arUtm = self::getUTMFromQuery()) {
            $result = $arUtm;
        } elseif ($arUtm = Application::getInstance()->getContext()->getRequest()->getCookie('utm')) {
            $result = json_decode($arUtm, true);
        }

        return $result;
    }


    /*
     * сохранение referer в Cookie
     */
    public static function saveRefererToCookie()
    {
        $result = false;
        $curSiteUrl = \COption::GetOptionString('main', 'site_name');

        if ($_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'], $curSiteUrl) === false) {

            $cookie = new Cookie('http_referer', $_SERVER['HTTP_REFERER']);
            $cookie->setDomain( $_SERVER['HTTP_HOST'] );

            $response = Application::getInstance()->getContext()->getResponse();
            $response->addCookie( $cookie );

            $result = true;
        }

        return $result;
    }

    /*
     * получение referer из Cookie
     */
    public static function getReferer()
    {
        $result = '';
        $curSiteUrl = \COption::GetOptionString('main', 'site_name');

        if ($_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'], $curSiteUrl) === false) {
            $result = $_SERVER['HTTP_REFERER'];
        } elseif ($referer = Application::getInstance()->getContext()->getRequest()->getCookie('http_referer')) {
            $result = $referer;
        }

        return $result;
    }
    
}