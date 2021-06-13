<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);

$iblocks = ['false' => Loc::getMessage("NO_SELECTED")];
if (Loader::includeModule('iblock')) {
    $res = \CIBlock::getList(['sort' => 'asc']);
    while ($row = $res->Fetch()) {
        $iblocks[$row['ID']] = $row['NAME'];
    }
}

$aTabs = array(
    array(
        "DIV" => "edit",
        "TAB" => Loc::getMessage("FOURPX_AUTOXML_OPTIONS_TAB_NAME"),
        "ICON" => "form_settings",
        "TITLE" => Loc::getMessage("FOURPX_AUTOXML_OPTIONS_TAB_TITLE"),
        "OPTIONS" => array(
            Loc::getMessage("FOURPX_AUTOXML_OPTIONS_TAB_TITLE_SUB"),
            Array("note" => Loc::getMessage("FOURPX_AUTOXML_OPTIONS_IBLOCK_NOTE")),
            array(
                "iblock",
                Loc::getMessage("FOURPX_AUTOXML_OPTIONS_IBLOCK"),
                "0",
                array("selectbox", $iblocks)
            ),
            /*array(
                "iblock_new",
                Loc::getMessage("FOURPX_AUTOXML_OPTIONS_IBLOCK_NEW"),
                "N",
                array(
                    "checkbox",
                    "N",
                    'onclick="this.form.iblock_new_code.disabled = !this.checked;"'
                )
            ),
            array(
                "iblock_new_code",
                Loc::getMessage("FOURPX_AUTOXML_OPTIONS_IBLOCK_NEW_CODE"),
                "",
                array("text", 30)
            ),*/
            array(
                "link_xml",
                Loc::getMessage("FOURPX_AUTOXML_OPTIONS_XML_LINK"),
                "",
                array("text", 30)
            ),
            /*array(
                "kron_period_h",
                Loc::getMessage("FOURPX_AUTOXML_OPTIONS_KRON_PERIOD_H"),
                "",
                array("text", 10)
            ),*/
        )
    ),
);


if($request->isPost() && check_bitrix_sessid()){

    foreach($aTabs as $aTab){
        foreach($aTab["OPTIONS"] as $arOption){
            if(!is_array($arOption)){
                continue;
            }
            if($arOption["note"]){
                continue;
            }
            if($request["apply"]){
                $optionValue = $request->getPost($arOption[0]);
                if($arOption[0] == "switch_on"){
                    if($optionValue == ""){
                        $optionValue = "N";
                    }
                }
                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG);
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

?>
<form name="autoxml_options" action="<?=($APPLICATION->GetCurPage()); ?>?mid=<?=($module_id); ?>&lang=<?=(LANG); ?>" method="post">
    <?
    foreach($aTabs as $aTab){
        if($aTab["OPTIONS"]){
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
        }
    }
    $tabControl->Buttons();
    ?>
    <input type="submit" name="apply" value="<? echo(Loc::GetMessage("FOURPX_AUTOXML_OPTIONS_INPUT_APPLY")); ?>" class="adm-btn-save" />
    <?=bitrix_sessid_post();?>
</form>

<?
$tabControl->End();
?>
<script>
    /*BX.ready(
        function(){
            var f = document.forms['autoxml_options'];

            if(f.iblock_new)
            {
                f.iblock_new_code.disabled = !f.iblock_new.checked;
            }
        }
    );*/
</script>