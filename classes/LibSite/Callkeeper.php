<?php


namespace LibSite;

/*
 * Класс для отправки данных в калкипер
 */
class Callkeeper
{

    /*
    * @params array $arPost данные для отправки callleper главное должен быть arPost['dealer']
     * в header.php разместить код if(!isset($_SESSION['httpreferer'])) $_SESSION['httpreferer'] = $_SERVER['HTTP_REFERER'];
    */
    public static function sendCallkeeper($arPost){
        \Bitrix\Main\Loader::includeModule('iblock');
        $el = new \CIBlockElement;
        $IBLOCK_ID = \LibSite\Helper::getIblockIdByCode('dealer');
        $res = \CIBlockElement::GetList(
            ['sort' => 'asc'],
            ['IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE'=>'Y', 'ID'=>$arPost['dealer']],
            false,
            [],
            ['ID', 'NAME', 'IBLOCK_ID', 'PROPERTY_CALLKEPER']
        );

        if($obj = $res->GetNext(true, false)) {
            $dealer = $obj;
        }

        $arPost['URL'] = $_SERVER['HTTP_REFERER'];

        $httpReferer = $_SESSION['httpreferer'];
        if(!stristr($httpReferer, 'rolf-ford')|| $httpReferer==""){
            $entry_point = $arPost['URL'];
            $_SESSION['httpreferer'] = $arPost['URL'];
        }
        else{
            $entry_point = $httpReferer;
        }

        if($httpReferer && strpos($httpReferer,'utm') !== false):
            $utm_ref = parse_url($httpReferer,PHP_URL_QUERY);
            parse_str($utm_ref,$utm_ref);
        endif;

        if(strpos($arPost['URL'],'?') !== false):
            $_GET = parse_url($arPost['URL'],PHP_URL_QUERY);
            parse_str($_GET,$_GET);
        endif;

        if(!empty($_GET['utm_source'])||!empty($_GET['utm_medium'])||!empty($_GET['utm_campaign'])):
            $utm = array(
                'utm_source' => (!empty($_GET['utm_source'])?$_GET['utm_source']:""),
                'utm_medium' => (!empty($_GET['utm_medium'])?$_GET['utm_medium']:""),
                'utm_campaign' => (!empty($_GET['utm_campaign'])?$_GET['utm_campaign']:""),
                'utm_content' => (!empty($_GET['utm_content'])?$_GET['utm_content']:""),
                'utm_term' => (!empty($_GET['utm_term'])?$_GET['utm_term']:""),
                'entry_point' => $entry_point,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            );
        else:
            if($utm_ref['utm_source'] || $utm_ref['utm_campaign']){
                $utm = $utm_ref;
                $utm['entry_point'] = $entry_point;
            }
            elseif(stristr($httpReferer, 'rolf-ford')|| $httpReferer==""){
                $utm = array(
                    'utm_type' => 'typein',
                    'entry_point' => $entry_point,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                );
            }
            elseif(stristr($httpReferer, 'yandex')){
                $utm = array(
                    'utm_type' => 'organic',
                    'utm_source' => 'yandex.ru',
                    'entry_point' => $entry_point,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                );
            }
            elseif(stristr($httpReferer, 'google')){
                $utm = array(
                    'utm_type' => 'organic',
                    'utm_source' => 'google.com',
                    'entry_point' => $entry_point,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                );
            }
            else{
                $utm = array(
                    'utm_type' => 'referral',
                    'utm_source' => parse_url($httpReferer, PHP_URL_HOST),
                    'entry_point' => $entry_point,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                );
            }
        endif;

        if (isset($_COOKIE['_ga'])) {
            list($version, $domainDepth, $cid1, $cid2) = preg_split('[\.]', $_COOKIE["_ga"], 4);
            $contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
            $cid = $contents['cid'];
        } else {
            $cid = self::gaGenUUID();
        }

        $url_vars = [
            'unique' => 'rolf-ford.ru',
            'apiak' => '2d14bb60f441b204',
            'whash' => 'e292b388281ff6ded5649ee68a91bd9b',
            'tool_name' => $arPost['name_form'],
            'ga_client_id' => $cid,
            'ip_client' => $_SESSION['SESS_IP'],
            'calls' => [
                [
                    'client' => $arPost["phone"],
                    'manager' => $dealer['PROPERTY_CALLKEPER_VALUE'],
                    //'office_name' => $dealer['PROPERTY_CALLKEEPER_OFFICE_VALUE'],
                    'current_page'=>$arPost['URL'],
                    'text_to_manager' => 'Посетитель заполнил форму на сайте.',
                    'site' => 'rolf-ford.ru',
                    'utm' => $utm,
                ],
            ],
        ];

        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/.log/add_callkeeper'.date('y-m-d_H-i-s').'-1.log',
            var_export([
                'date'=>date("d.m.Y H:i:s"),
                'url_vars'=>$url_vars,
                'arPost'=>$arPost,
                'utm_ref'=>$utm_ref,
                '$_SESSION_httpreferer'=> $httpReferer,
                'parse_url'=>parse_url($httpReferer,PHP_URL_QUERY)
            ], true)
        );

        $protocol = 'https://';
        $url = $protocol . 'api.callkeeper.ru/makeCalls?';
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($url_vars),
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
    }

    public static function gaGenUUID(){
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

    }
}