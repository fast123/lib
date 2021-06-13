<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["FILTER_VIEW_MODE"] = "VERTICAL";
$arParams["POPUP_POSITION"] = "right";

$arResult['ARR_SELECT_FIELD'] = ['YEAR', 'RUN'];

$arResult["ARR_MIN_YEAR"] = ['1995', '1996', '1997', '1998', '1999', '2000', '2001', '2002', '2003', '2004',
    '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017',
    '2018', '2019', '2020',
];
$arResult["ARR_MAX_YEAR"] = $arResult["ARR_MIN_YEAR"];
$arResult['ARR_MIN_RUN'] = [
    "0" => "0",
    "10000" => "10 000",
    "25000" => "25 000",
    "35000" => "35 000",
    "50000" => "50 000",
    "75000" => "75 000",
    "100000" => "100 000",
    ];
$arResult['ARR_MAX_RUN'] = $arResult['ARR_MIN_RUN'];
$arResult['ARR_MAX_RUN'][''] = 'выше 100 000';