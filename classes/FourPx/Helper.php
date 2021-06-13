<?php

namespace FourPx;
use Bitrix\Main\Mail\Event;

class Helper {

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

    /*
     * Получение id инфоблока по сиимвольному коду
     * @param string $arIblockCode
     */
    public static function getIblockIdByCode($arIblockCode) {
        $arIblockCode = (string)$arIblockCode;
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        $cacheId = md5($arIblockCode);
        $cacheInitDir = 'iblock_id_by_code';

        if ($cache->initCache(36000, $cacheId, $cacheInitDir))
        {
            $arIblockID = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            if (\Bitrix\Main\Loader::includeModule('iblock')) {
                if ($rsIblocks = \CIBlock::GetList([], ['CODE' => $arIblockCode])) {
                    if ($iblock = $rsIblocks->Fetch()) {
                        $arIblockID = $iblock['ID'];
                    }
                }
            }
            $cache->endDataCache($arIblockID);
        }
        return $arIblockID;
    }


}