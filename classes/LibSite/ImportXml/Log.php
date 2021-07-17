<?
namespace LibSite\ImportXml;

use \Bitrix\Main\Application,
    \Bitrix\Main\Diag\Debug;

/**
 * Trait Log
 * @package ImportXml
 */

class Log
{
    const LOG_DIR = '/local/log/';

    public static function start($name, $value = '')
    {
        if ($value) {
            Debug::startTimeLabel($name . ': ' . $value);
        } else {
            Debug::startTimeLabel($name);
        }
    }

    public static function end($name, $value = '')
    {
        if ($value) {
            Debug::endTimeLabel($name . ': ' . $value);
        } else {
            Debug::endTimeLabel($name);
        }
    }

    public static function save($fileName, $title = '')
    {
        $arTimeLabels = Debug::getTimeLabels();

        foreach ($arTimeLabels as $labelName => $label) {
            $time = $label['time'];
            $h = floor($time / 3600);
            $m = floor( ($time - h * 3600) / 60);
            $s = $time - $h * 3600 - $m * 60;
            $label['h'] = str_pad($h, 2, '0', STR_PAD_LEFT);
            $label['m'] = str_pad($m, 2, '0', STR_PAD_LEFT);
            $label['s'] = str_pad(number_format($s, 2, '.', ''), 5, '0', STR_PAD_LEFT);
            $arTimeLabels[ $labelName ] = $label['h'] . 'ч ' . $label['m'] . 'м ' . $label['s'] . 'с';
        }

        $logDirPath = self::LOG_DIR;

        if (! is_dir($logDirPath)) {
            $isDirExists = mkdir($logDirPath, 0755, true);
        } else {
            $isDirExists = true;
        }

        if ($isDirExists) {
            Debug::writeToFile(
                $arTimeLabels,
                $title ?: date('H:i:s'),
                $logDirPath . self::rusToLat($fileName) . '.log'
            );
        }
    }


    private static function rusToLat($source) {
        $rus = [
            '\\', '/', ' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
        ];
        $lat = [
            '\\', '/', '_', 'A', 'B', 'V', 'G', 'D', 'E', 'Yo', 'Zh', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Shh', '``', 'Y', '`', 'E`', 'Yu', 'Ya',
            'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shh', '``', 'y', '`', 'e`', 'yu', 'ya'
        ];
        return str_replace($rus, $lat, $source);
    }
}
