<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Application;

$connection =\Bitrix\Main\Application::getConnection();
$sqlHelper = $connection->getSqlHelper();

$sql = "SELECT ORDER_ID, EXTERNAL_UUID, DATE_PRINT_START, DATE_PRINT_END, STATUS, SUM, COUNT(*) AS CNTDUBL FROM b_sale_cashbox_check 
                        WHERE STATUS= 'Y'
                        GROUP BY ORDER_ID
                        HAVING COUNT(*) > 1";
$recordset = $connection->query($sql);
$alDubl = $recordset->getSelectedRowsCount();
while ($record = $recordset->fetch()) {
    $arResult[] = $record;
}

?>
<style>
    td{
        padding: 10px;
    }
</style>
<div>Дублей <?=$alDubl?></div>
<table border="1px">
    <tr>
        <td>ORDER_ID</td>
        <td>EXTERNAL_UUID (видимо один из id дублей чека)</td>
        <td>DATE_PRINT_START</td>
        <td>DATE_PRINT_END</td>
        <td>SUM</td>
        <td>CNTDUBL</td>
    </tr>
    <?foreach ($arResult as $item):?>
        <tr>
            <td><?=$item['ORDER_ID']?></td>
            <td><?=$item['EXTERNAL_UUID']?></td>
            <td><?=$item['DATE_PRINT_START']?></td>
            <td><?=$item['DATE_PRINT_END']?></td>
            <td><?=$item['SUM']?></td>
            <td><?=$item['CNTDUBL']?></td>
        </tr>
    <?endforeach?>
</table>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
