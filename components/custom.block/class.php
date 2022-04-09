<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CCustomBlock extends CBitrixComponent
{

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }


    public function executeComponent()
    {
		if ($this->StartResultCache())
		{
			$this->IncludeComponentTemplate();
		}
    }
}