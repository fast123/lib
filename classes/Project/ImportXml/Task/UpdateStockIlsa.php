<?
namespace Project\ImportXml\Task;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Loader;
use Project\ImportXml\Log;


/**
 * Class UpdateRefs
 * @package ImportXml\Task
 */

class UpdateStockIlsa extends Task
{
    const NAME = 'update_stock_ilsa';
    public $moduleId;
    public $link_xml;
    public $tagParentList;
    public $feedIsUpdate;
    public $feedDataArray;
    public $feedHash;
    public $error;
    public $allIBProperties;
    public $elementObject;
    public $cronMode = false;
    public $lastImport;
    public $logIsEmpty;
    public $stockIbCode;
    public $arChangeCode = [
        'id'=>'unique_id',
        'dealer'=>'dealer_name',
        'brand'=>'mark_id',
        'bodyConfiguration'=>'body_type',
        'modification'=>'modification_id',
        'complectation'=>'complectation_name',
        'brandComplectationCode'=>'complectation_code',

        'engineType'=>'fuel',
        'engineVolume'=>'volume',


        'enginePower'=>'horse_power',
        'bodyDoorCount'=>'doors_count',
        'bodyColor'=>'color',
        'bodyColorMetallic'=>'metallic',
        'gearboxType'=>'transmission',
        'gearboxGearCount'=>'transmission_count',
        'steeringWheel'=>'wheel',
        'mileage'=>'run',
        'mileageUnit'=>'run_unit',
        'priceWithDiscount'=>'discount_price',
        'tradeinDiscount'=>'tradein_discount',
        'creditDiscount'=>'credit_discount',
        'insuranceDiscount'=>'insurance_discount',
        'brandColorCode'=>'color_code',
        'brandInteriorCode'=>'interior_code'
    ];

    /*
        @param string $link_xml ссылка на фид
        @param string $tagElementList родительский элемент списка фида, наприпер для autoru - car
    */
    public function __construct($link_xml, $tagElementList)
    {
        $this->importName = 'Обновление стока Илса. Фид '.$link_xml;
        $this->stockIbCode = 'stockDetailFed';

        Log::start('Получение файла');
        $this->getDataFed($link_xml, $tagElementList);
        $this->renameCode();
        Log::end('Получение файла');
    }

    protected function getDataFed($link_xml, $tagElementList){
        #TODO Добавить логирование и вывод ошибок
        $this->link_xml = $link_xml;
        $this->tagElementList = $tagElementList;
        $feedFileString = file_get_contents($this->link_xml);
        $simpleXmlObj = simplexml_load_string($feedFileString, 'SimpleXMLElement', LIBXML_NOCDATA);
        $simpleXmlArray = json_decode(json_encode($simpleXmlObj), TRUE);
        $this->tmpDataArray = $simpleXmlArray[$tagElementList];
        $this->feedHash = md5($feedFileString);

        if($simpleXmlObj == false) $this->error = true;
    }

    /*
     * Замена тегов/ключей под формат auto.ru
     */
    protected function renameCode(){
        foreach ($this->tmpDataArray as $key=>$arItem){
            $arNewItem = [];
            foreach ($arItem as $code=>$prop){

                if($code=='equipment'){
                    $arNewItem['equipments_installed'] = $prop;
                }
                elseif($code=='photos'){
                    if(is_array($prop['photo'])){
                        $arNewItem['images']['image'] = $prop['photo'];
                    }
                    else{
                        $arNewItem['images']['image'][] = $prop['photo'];
                    }
                }
                elseif($code=='phones'){
                    continue;
                }
                elseif(is_array($prop)) {
                    $arNewItem[$code] = $prop[0];
                }
                else{
                    $arNewItem[$code] = $prop;
                }


                if( array_key_exists($code, $this->arChangeCode) ){
                    $arNewItem[ $this->arChangeCode[ $code ] ] = $arNewItem[$code];
                    unset($arNewItem[$code]);
                }

            }

            $this->feedDataArray[$key] = $arNewItem;
        }

        return $feedDataArray;
    }


    protected function executeMethod()
    {

        $stockIbId = \Project\IBlock::getIblockIdByCodes($this->stockIbCode);

        # получение списка дилеров
        /*$refDealers = \Progect\HLBlock::getList('Dealers', [
            'select' => ['UF_ID']
        ], 'UF_ID');*/

        #TODO логирование
        if (empty($this->feedDataArray) || $this->error) return;


        Log::start('Обновление стока');
        //$test = \Progect\IBlock::isIblockExists( $this->stockIbCode );
        $keyField = 'unique_id';#TODO добавить в параметры конструктора id элемента в рамках фида
        $nameFiled = 'mark_id';#TODO добавить в параметры конструктора name элемента в рамках фида

        if (\Project\IBlock::isIblockExists( $this->stockIbCode )) {
            \Project\IBlock::fillIbWithData(
                $this->stockIbCode,
                $this->feedDataArray,
                $keyField,
                $nameFiled,
                [
                    'IBLOCK_ID' => $stockIbId
                ]
            );
        } else {
            $IbProps = \Project\IBlock::prepareFieldsToCreateIBlockFromData($this->feedDataArray);

            if (\Project\IBlock::createIblock($this->stockIbCode, $IbProps)) {
                \Project\IBlock::fillIbWithData(
                    $this->stockIbCode,
                    $this->feedDataArray,
                    $keyField,
                    $nameFiled,
                    [
                        'IBLOCK_ID' => $stockIbId
                    ]
                );
            }
        }

        Log::end('Обновление стока');


    }
}
