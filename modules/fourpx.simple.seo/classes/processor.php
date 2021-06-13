<?php
namespace FourPX\SimpleSEO;

use Bitrix\Main\Config\Option,
    Bitrix\Main\Application,
    Bitrix\Main\Page\Asset;

class Processor
{
	const MODULE_ID = 'fourpx.simple.seo';
    const TABLE_ID = 'fpx_simpleseo';
    const SEO_IBLOCK_CODE = 'fourpx.simple.seo.sitemap';

    public static function getMeta()
    {
        $result = [];

        $sqlHelper = Application::getConnection()->getSqlHelper();

        $requestURI = $_SERVER['REQUEST_URI'];
        if (strpos($requestURI, '?') !== false) {
            $requestURI = substr($requestURI,0,strpos($requestURI, '?'));
        }

        $query = "
            SELECT
              `title` `TITLE`,
              `h1` `H1`,
              `description` `DESCRIPTION`,
              `keywords` `KEY_WORDS`,
              `seo_text` `SEO_TEXT`,
              `seo_text_2` `SEO_TEXT_2`,
              `disclaimer` `DISCLAIMER`,
              `link_rel_alternative` `LINK_REL_ALTERNATIVE`
            FROM `" . self::TABLE_ID . "`
            WHERE `is_active` = 'Y'
            AND `url_tech` = '" . $sqlHelper->forSql($requestURI) . "'
            LIMIT 1;
        ";

        if ($rsRecords = Application::getConnection()->query($query))
        {
            if ($rsRecords->getSelectedRowsCount() == 1) {
                $result = $rsRecords->fetch();
            }
        }

        return $result;
    }

    public static function setMeta()
    {
        global $APPLICATION;

        $sqlHelper = Application::getConnection()->getSqlHelper();

        $requestURI = $_SERVER['REQUEST_URI'];
        if (strpos($requestURI, '?') !== false) {
            $requestURI = substr($requestURI, 0, strpos($requestURI, '?'));
        }

        $query = "
            SELECT
              `title` `TITLE`,
              `h1` `H1`,
              `description` `DESCRIPTION`,
              `keywords` `KEY_WORDS`,
              `seo_text` `SEO_TEXT`,
              `seo_text_2` `SEO_TEXT_2`,
              `disclaimer` `DISCLAIMER`,
              `link_rel_canonical` `LINK_REL_CANONICAL`,
              `link_rel_alternative` `LINK_REL_ALTERNATIVE`
            FROM `" . self::TABLE_ID . "`
            WHERE `is_active` = 'Y'
            AND `url` = '" . $sqlHelper->forSql($requestURI) . "'
            LIMIT 1;
        ";

        if ($rsRecords = Application::getConnection()->query($query))
        {
            if ($rsRecords->getSelectedRowsCount() == 1) {
                $record = $rsRecords->fetch();

                if ($record['TITLE']) {
                    $APPLICATION->SetTitle(htmlspecialchars($record['TITLE']), true);
                    $APPLICATION->SetPageProperty('title', htmlspecialchars($record['TITLE']));
                }
                if ($record['DESCRIPTION'])
                    $APPLICATION->SetPageProperty('description', htmlspecialchars($record['DESCRIPTION']));
                if ($record['H1'])
                    $APPLICATION->SetPageProperty('h1', htmlspecialchars($record['H1']));
                if ($record['KEY_WORDS'])
                    $APPLICATION->SetPageProperty('keywords', htmlspecialchars($record['KEY_WORDS']));
                if ($record['SEO_TEXT'])
                    $APPLICATION->SetPageProperty('seo-text', '
                        <div class="seo-text">
                            ' . $record['SEO_TEXT'] . '
                        </div>
                    ');
                if ($record['SEO_TEXT_2'])
                    $APPLICATION->SetPageProperty('seo-text-2', '
                        <div class="seo-text-2">
                            ' . $record['SEO_TEXT_2'] . '
                        </div>
                    ');
                if ($record['DISCLAIMER'])
                    $APPLICATION->SetPageProperty('disclaimer', htmlspecialchars($record['DISCLAIMER']));
                if ($record['LINK_REL_ALTERNATIVE'])
                    Asset::getInstance()->addString('<link rel="alternate" media="only screen and (max-width: 640px)" href="' . $record['LINK_REL_ALTERNATIVE'] . '"/>', true);
                if ($record['LINK_REL_CANONICAL'])
                    Asset::getInstance()->addString('<link rel="canonical" href="' . $record['LINK_REL_CANONICAL'] . '"/>', true);

                $APPLICATION->SetPageProperty('disclaimer', htmlspecialchars($record['DISCLAIMER']));
            }
        }

        return true;
    }

    public static function getList($arSelect = ['*'])
    {
        if (! is_array($arSelect)) {
            $arSelect = ['*'];
        }
        if (empty(array_intersect(['*', 'ID'], $arSelect))) {
            $arSelect[] = 'ID';
        }

        $sqlHelper = Application::getConnection()->getSqlHelper();

        $query = "
            SELECT
                " . implode(",", $arSelect) . "
            FROM `" . self::TABLE_ID . "`
            WHERE `is_active` = 'Y'
            ORDER BY `sort` ASC, `h1` ASC
        ";

        if ($rsRecords = Application::getConnection()->query($query))
        {
            return $rsRecords;
        }

        return false;
    }

    public static function redirectToMobile()
    {
        $SEOData = self::getMeta();

        if ($SEOData['LINK_REL_ALTERNATIVE']) {
            LocalRedirect( $SEOData['LINK_REL_ALTERNATIVE'] );
        } else {
            LocalRedirect('http://m.hyundai-avtorus.ru/');
        }
    }

    public static function __callStatic($name, array $params)
    {
    }
}

?>
