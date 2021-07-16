<?
namespace Project\ImportXml\Task;

use Bitrix\Main\Web\HttpClient;
use \Project\ImportXml\Log;


/**
 * Class Task
 * @package ImportXml\Task
 */


abstract class Task
{
    const NAME = '';
    public $importName;

    public function execute()
    {
        Log::start(static::NAME);

        $this->executeMethod();

        Log::end(static::NAME);
        Log::save(static::NAME, $this->importName);
    }

    protected function executeMethod()
    {

    }
}
