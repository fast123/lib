<?
namespace FourPx\Sale;

use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\Order;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\TradingPlatform\OrderTable;
use Bitrix\Main\Application;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Helpers\Admin\Blocks\OrderStatus;

/*
 * блока отмены заказа в админке
 */
class OrderAdminCancel extends OrderStatus{

    public static function prepareData(Order $order)
    {
        static $result = null;

        if($result === null)
        {
            $creator = static::getUserInfo($order->getField("CREATED_BY"));

            if(strlen($order->getField("CREATED_BY")) > 0)
                $creatorName = OrderEdit::getUserName($order->getField("CREATED_BY"), $order->getSiteId());
            else
                $creatorName = "";

            if(strlen($order->getField("EMP_CANCELED_ID")) > 0)
                $cancelerName = OrderEdit::getUserName($order->getField("EMP_CANCELED_ID"), $order->getSiteId());
            else
                $cancelerName = "";

            $sourceName = "";

            if(strlen($order->getField('XML_ID')) > 0)
            {
                $dbRes = OrderTable::getList(array(
                    'filter' => array(
                        'ORDER_ID' => $order->getId()
                    ),
                    'select' => array('SOURCE_NAME' => 'TRADING_PLATFORM.NAME')
                ));

                if($tpOrder = $dbRes->fetch())
                    $sourceName = $tpOrder['SOURCE_NAME'];
            }

            $result = array(
                "DATE_INSERT" => $order->getDateInsert()->toString(),
                "DATE_UPDATE" => $order->getField('DATE_UPDATE')->toString(),
                "CREATOR_USER_NAME" => $creatorName,
                "CREATOR_USER_ID" => $creator["ID"],
                "STATUS_ID" => $order->getField('STATUS_ID'),
                "CANCELED" => $order->getField("CANCELED"),
                "EMP_CANCELED_NAME" => $cancelerName,
                "SOURCE_NAME" => $sourceName
            );

            if(intval($order->getField('AFFILIATE_ID')) > 0)
            {
                $result["AFFILIATE_ID"] = intval($order->getField('AFFILIATE_ID'));

                $dbAffiliate = \CSaleAffiliate::GetList(
                    array(),
                    array("ID" => $result["AFFILIATE_ID"]),
                    false,
                    false,
                    array("ID", "USER_ID")
                );

                if($arAffiliate = $dbAffiliate->Fetch())
                {
                    $result["AFFILIATE_ID"] = $arAffiliate["ID"];
                    $result["AFFILIATE_NAME"] = OrderEdit::getUserName($arAffiliate["USER_ID"], $order->getSiteId());
                }
                else
                {
                    $result["AFFILIATE_ID"] = 0;
                    $result["AFFILIATE_NAME"] = "-";
                }
            }
        }

        return $result;
    }

    public static function getCancelBlockHtml(Order $order, array $data, $orderLocked = false, $type = 'view')
    {
        $isCanceled = ($order->getField('CANCELED') == "Y" ? true : false);

        if($isCanceled)
        {
            $text = '
				<div class="adm-s-select-popup-element-selected" id="sale-adm-status-cancel-blocktext">
					<div class="adm-s-select-popup-element-selected-bad">
						<span>'.Loc::getMessage("SALE_ORDER_STATUS_CANCELED").'</span>
						'.$order->getField('DATE_CANCELED').'
						<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='. $order->getField("EMP_CANCELED_ID").'">'.
                htmlspecialcharsbx($data["EMP_CANCELED_NAME"]).
                '</a>
					</div>
				</div>';
        }
        else
        {
            $text = '
				<div class="adm-s-select-popup-element-selected" style="text-align:center;" id="sale-adm-status-cancel-blocktext">
					<a href="javascript:void(0);" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();">
						'.Loc::getMessage("SALE_ORDER_STATUS_CANCELING").'
					</a>
				</div>';
        }

        $reasonCanceled = trim($order->getField("REASON_CANCELED"));

        if(!\CSaleYMHandler::isOrderFromYandex($order->getId()))
        {
            $subStatusOreder = [
                '',
                'Не устраивает стоимость товара',
                'Не устраивает стоимость доставки',
                'Не устраивают сроки доставки',
                'Не устраивают сроки обработки заказа',
                'Уже заказал/купил в другом месте',
                'Передумал',
                'Укажите иное'
            ];
            if($isCanceled){
                $reasonHtml = '
                    <div class="adm-s-select-popup-modal-title">'.Loc::getMessage("SALE_ORDER_STATUS_COMMENT").' - '.$reasonCanceled.'</div>
                    <div id="cancel_order_textarea">
                        <textarea style="width:400px;min-height:100px; display:none;" name="FORM_REASON_CANCELED" id="FORM_REASON_CANCELED"></textarea>
                    </div>
                ';
            }
            else{
                $reasonHtml = '
				<div class="adm-s-select-popup-modal-title">'.Loc::getMessage("SALE_ORDER_STATUS_COMMENT").'</div>
				<select style="width:412px;" id="cancel_order_select" name="FORM_REASON_CANCELED" onchange="BX.Sale.Admin.OrderEditPage.SetOption(this)">';

                foreach ($subStatusOreder as $statusId => $statusName) {
                    $reasonHtml .= '<option>' . htmlspecialcharsbx($statusName) . '</option>';
                }

                $reasonHtml .= '</select>';
                $reasonHtml .= '
                <br><br>
                <div id="cancel_order_textarea">
				    <textarea style="width:400px;min-height:100px; display:none;" name="FORM_REASON_CANCELED" id="FORM_REASON_CANCELED"></textarea>
				</div>
			';
            }
        }
        else
        {
            $reasonHtml = '
				<div class="adm-s-select-popup-modal-title">'.Loc::getMessage("SALE_ORDER_STATUS_CANCELING_REASON").'</div>
				<select name="FORM_REASON_CANCELED" style="max-width: 400px;" id="FORM_REASON_CANCELED" class="adm-bus-select"'.($isCanceled ? ' disabled' : '' ).'>';

            foreach (\CSaleYMHandler::getOrderSubstatuses() as $statusId => $statusName)
                $reasonHtml .= '<option value="'.$statusId.'"'.($statusId == $reasonCanceled ? " selected" : "").'>'.htmlspecialcharsbx($statusName).'</option>';

            $reasonHtml .= '</select>';
        }

        if($type=='edit'){
            $resultHtml = '
					<div class="adm-s-select-popup-box" style="margin: auto; display: block !important;">
						<div class="adm-s-select-popup-container">'.
            ($orderLocked ? '' : '<div class="adm-s-select-popup-element-selected-control" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();"></div>').
            $text.
            '</div>
						<div class="adm-s-select-popup-modal /*active*/" id="sale-adm-status-cancel-dialog">
							<div class="adm-s-select-popup-modal-content">
								'.$reasonHtml.'
								<div class="adm-s-select-popup-modal-desc">'.Loc::getMessage("SALE_ORDER_STATUS_USER_CAN_VIEW").'</div>
								<span class="adm-btn" id="sale-adm-status-cancel-dialog-btn" onclick="BX.Sale.Admin.OrderEditPage.onCancelStatusButton(\''.$order->getId().'\',\''.$data["CANCELED"].'\');">
									'.($data["CANCELED"] == "N" ? Loc::getMessage("SALE_ORDER_STATUS_CANCEL") : Loc::getMessage("SALE_ORDER_STATUS_CANCEL_CANCEL")).'
								</span>
								<span class="adm-s-select-popup-modal-close" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();">'.Loc::getMessage("SALE_ORDER_STATUS_TOGGLE").'</span>
							</div>
						</div>
					</div>
				';
        }
        else{
            $resultHtml = '<tr id="sale-adm-status-cancel-row"><td class="adm-detail-content-cell-l"></td>';
            $resultHtml .= '
				<td class="adm-detail-content-cell-r">
					<div class="adm-s-select-popup-box">
						<div class="adm-s-select-popup-container">'.
            ($orderLocked ? '' : '<div class="adm-s-select-popup-element-selected-control" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();"></div>').
            $text.
            '</div>
						<div class="adm-s-select-popup-modal /*active*/" id="sale-adm-status-cancel-dialog">
							<div class="adm-s-select-popup-modal-content">
								'.$reasonHtml.'
								<div class="adm-s-select-popup-modal-desc">'.Loc::getMessage("SALE_ORDER_STATUS_USER_CAN_VIEW").'</div>
								<span class="adm-btn" id="sale-adm-status-cancel-dialog-btn" onclick="BX.Sale.Admin.OrderEditPage.onCancelStatusButton(\''.$order->getId().'\',\''.$data["CANCELED"].'\');">
									'.($data["CANCELED"] == "N" ? Loc::getMessage("SALE_ORDER_STATUS_CANCEL") : Loc::getMessage("SALE_ORDER_STATUS_CANCEL_CANCEL")).'
								</span>
								<span class="adm-s-select-popup-modal-close" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();">'.Loc::getMessage("SALE_ORDER_STATUS_TOGGLE").'</span>
							</div>
						</div>
					</div>
				</td>';
            $resultHtml .= '<tr>';
        }

        return $resultHtml;
    }

}



?>