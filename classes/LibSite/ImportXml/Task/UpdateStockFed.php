<?
namespace LibSite\ImportXml\Task;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Loader;
use LibSite\ImportXml\Log;


/**
 * Class UpdateRefs
 * @package ImportXml\Task
 */

class UpdateStockFed extends Task
{
    const NAME = 'update_stock';
    public $moduleId;
    public $link_xml;
    public $tagParentList;
    public $feedIsUpdate;
    public $feedDataArray;
    public $feedHash;
    public $allIBProperties;
    public $elementObject;
    public $cronMode = false;
    public $lastImport;
    public $logIsEmpty;
    public $stockIbCode;

    /*
        @param string $link_xml ссылка на фид
        @param string $tagParentList родительский элемент списка фида, наприпер для autoru - cars
        @param string $tagElementList родительский элемент списка фида, наприпер для autoru - car
    */
    public function __construct($link_xml, $tagParentList, $tagElementList)
    {
        $this->importName = 'Обновление стока. Фид '.$link_xml;
        $this->stockIbCode = 'stockDetailFed';

        Log::start('Получение файла');
        $this->getDataFed($link_xml, $tagParentList, $tagElementList);
        Log::end('Получение файла');
    }

    protected function getDataFed($link_xml, $tagParentList, $tagElementList){
        #TODO Добавить логирование и вывод ошибок
        $this->link_xml = $link_xml;
        $this->tagParentList = $tagParentList;
        $this->tagElementList = $tagElementList;
        $feedFileString = file_get_contents($this->link_xml);
        $simpleXmlObj = simplexml_load_string($feedFileString);
        $simpleXmlArray = json_decode(json_encode($simpleXmlObj->{$tagParentList}), TRUE);
        $this->feedDataArray = $simpleXmlArray[$tagElementList];
        $this->feedHash = md5($feedFileString);
    }


    protected function executeMethod()
    {

        $stockIbId = \LibSite\IBlock::getIblockIdByCodes($this->stockIbCode);

        # получение списка дилеров
        /*$refDealers = \Progect\HLBlock::getList('Dealers', [
            'select' => ['UF_ID']
        ], 'UF_ID');*/

        #TODO логирование
        if (empty($this->feedDataArray)) return;



        Log::start('Обновление стока');
        //$test = \Progect\IBlock::isIblockExists( $this->stockIbCode );
        $keyField = 'unique_id';#TODO добавить в параметры конструктора id элемента в рамках фида
        $nameFiled = 'mark_id';#TODO добавить в параметры конструктора name элемента в рамках фида

        if (\LibSite\IBlock::isIblockExists( $this->stockIbCode )) {
            \LibSite\IBlock::fillIbWithData(
                $this->stockIbCode,
                $this->feedDataArray,
                $keyField,
                $nameFiled,
                [
                    'IBLOCK_ID' => $stockIbId
                ]
            );
        } else {
            $IbProps = \LibSite\IBlock::prepareFieldsToCreateIBlockFromData($this->feedDataArray);

            if (\LibSite\IBlock::createIblock($this->stockIbCode, $IbProps)) {
                \LibSite\IBlock::fillIbWithData(
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
