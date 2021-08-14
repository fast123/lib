<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Config;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Result;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\ServiceResult;
use Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);


/**
 * Class TestPaymentHandler
 * название класса обработчика должно совпадать с названием папки обработчика и иметь окончание Handler.
 * Например, название папки - test_payment, а название класса - TestPaymentHandler.
 *
 * @package Sale\Handlers\PaySystem
 */
class test_pymentHandler extends PaySystem\ServiceHandler implements PaySystem\IPrePayable
{

    /**
     * @param Payment $payment
     * @param Request|null $request
     * @return ServiceResult
     */
    public function initiatePay(Payment $payment, Request $request = null)
    {
        //пример установки доп параметров, поверх тех что задаются в админке (поля создаются в файле .descripton.php)
        $params = array(
            'PARAM1' => 'VALUE1',
            'PARAM2' => 'VALUE2',
        );
        $this->setExtraParams($params);

        //вывзов шаблона из папки template
        return $this->showTemplate($payment, "payment");
    }

    /**
     * Должен вернуть массив со списком валют
     * @return array
     */
    public function getCurrencyList()
    {
        return ['RUB'];
    }

    /**
     * Метод должен вернуть идентификатор оплаты (не заказа!) из $request при возврате информации ОТ ПЛАТЕЖНОСЙ СИСТЕМЫ
     * @param Request $request
     * @return mixed
     */
    public function getPaymentIdFromRequest(Request $request)
    {
        $paymentId = $request->get('ORDER');
        $paymentId = preg_replace("/^[0]+/","",$paymentId);
        return intval($paymentId);
    }

    /**
     * должна вернуть массив полей, по которым проверяется принадлежность информации при возврате к данному обработчику,
     * при этом функция может вернуть как ассоциативный массив, при этом проверка будет
     * производится по значениям полей, так и неассоциативный - при этом проверка будет производится только по наличию
     * полей в $request.
     * @return array
     */
    public static function getIndicativeFields()
    {
        return array('PARAM1','PARAM2');
    }

    /**
     * осуществляет дополнительную проверку на принадлежность результат пс к обработчику, и она вызывается после
     * getIndicativeFields. Если дополнительные проверки не требуются, вы можете просто вернуть true.
     * @param Request $request параметры запросы возвращеные от платежной системы
     * @param $paySystemId
     * @return bool
     */
    static protected function isMyResponseExtended(Request $request, $paySystemId)
    {
        return true;
    }

    /**
     *  Выполняет обработку результата от платежной системы, и вызов ее произойдет только в том случае,
     * если вы правильно написали getIndicativeFields и isMyResponseExtended.
     * В самом упрощенном виде эта функция может выглядеть как-то так
     * @param Payment $payment клас обработчика
     * @param Request $request параметры от платежной системы полсе обработки
     * @return ServiceResult
     */
    public function processRequest(Payment $payment, Request $request)
    {
        $result = new PaySystem\ServiceResult();
        $action = $request->get('ACTION');
        $data = $this->extractDataFromRequest($request);
        $data['CODE'] = $action;

        if ($action === "1") {
            $result->addError(new Error("Ошибка платежа"));
        } elseif ($action === "0") {
            $fields = array(
                "PS_STATUS_CODE" => $action,
                "PS_STATUS_MESSAGE" => '',
                "PS_SUM" => $request->get('AMOUNT'),
                "PS_CURRENCY" => $payment->getField('CURRENCY'),
                "PS_RESPONSE_DATE" => new DateTime(),
                "PS_INVOICE_ID" => '',
            );

            if ($this->isCorrectSum($payment, $request)) {
                $data['CODE'] = 0;
                $fields["PS_STATUS"] = "Y";
                $fields['PS_STATUS_DESCRIPTION'] = "Оплата произведена успешно";
                $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
            } else {
                $data['CODE'] = 200;
                $fields["PS_STATUS"] = "N";
                $message = "Неверная сумма платежа";
                $fields['PS_STATUS_DESCRIPTION'] = $message;
                $result->addError(new Error($message));
            }
            $result->setPsData($fields);
        } else {
            $result->addError(new Error("Неверный статус платежной системы при возврате информации о платеже"));
        }

        $result->setData($data);

        //логирование в случае ошибок
        //вывести можно Bitrix\Sale\Internals\PaySystemErrLogTable::getList(array('order'=>array('ID'=>'DESC')));
        //таблица b_sale_pay_system_err_log
        if (!$result->isSuccess()) {
            PaySystem\ErrorLog::add(
                array(
                    'ACTION' => "processRequest",
                    'MESSAGE' => join('\n', $result->getErrorMessages())
                )
            );
        }

        return $result;
    }

    public function initPrePayment(Payment $payment = null, Request $request)
    {
        return true;
    }

    public function getProps()
    {
        $data = array();
        return $data;
    }

    public function payOrder($orderData = array())
    {
    }

    public function setOrderConfig($orderData = array())
    {
        if ($orderData)
            $this->prePaymentSetting = array_merge($this->prePaymentSetting, $orderData);
    }

    public function basketButtonAction($orderData)
    {
        return true;
    }
}