<?
namespace LibSite;
/*
 * Клас для генерации изображения разрешнеия webp
 */
class Webp {

    public static function getWebPath($pathOrig)
    {
        if (! function_exists('gd_info') || gd_info()['WebP Support'] != 1) {
            return false;
        }

        $arOrigPathParts = explode('?', $pathOrig);
        $pathOrig = array_shift( $arOrigPathParts );

        $pathNew = str_replace(
            ['/upload/', '.jpg', '.png', '.bmp', '.png'],
            ['/upload/webp/', '.webp', '.webp', '.webp', '.webp'],
            $pathOrig
        );

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $pathNew)) {
            return $pathNew;
        }

        try {
            $im = self::imageCreateFromAny($_SERVER['DOCUMENT_ROOT'] . $pathOrig);
            if (! $im) {
                return false;
            }

            $newDir = $_SERVER['DOCUMENT_ROOT'] . preg_replace('/^(.*)\/(.*?)$/', '$1/', $pathNew);
            if (! is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }

            $res = imagewebp($im, $_SERVER['DOCUMENT_ROOT'] . $pathNew);
            imagedestroy($im);

            return $res ? $pathNew : false;
        } catch(\Throwable $e) {
            return false;
        }
    }

    private static function imageCreateFromAny($filepath) {
        try {
            $type = exif_imagetype($filepath);

            if (! in_array($type, [1, 2, 3, 6])) {
                return false;
            }

            switch ($type) {
                case 1 :
                    $im = imageCreateFromGif($filepath);
                    imagepalettetotruecolor($im);
                    break;
                case 2 :
                    $im = imageCreateFromJpeg($filepath);
                    imagepalettetotruecolor($im);
                    break;
                case 3 :
                    $im = imageCreateFromPng($filepath);
                    imagepalettetotruecolor($im);
                    imagealphablending($im, true);
                    imagesavealpha($im, true);
                    break;
                case 6 :
                    $im = imageCreateFromBmp($filepath);
                    imagepalettetotruecolor($im);
                    break;
            }
            return $im;
        } catch(\Throwable $e) {
            return false;
        }
    }
}
