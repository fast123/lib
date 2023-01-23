<?php

namespace LibSite\RestApi;

use Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    LibSite\RestApi\UsersTable,
    LibSite\RestApi\HTTPcodes;

/**
 *
 */
class Tools {

    protected $page;
    protected $limit;
    protected $offset;
    protected $countAll;

    public function __construct(int $page, int $limit){
        $this->page = $page>0 ? $page : 1;
        $this->limit = $limit;
        $this->offset = 0;
        if($this->page>1){
            $this->offset = ($this->limit * $this->page) - $this->limit;
        }
    }

    public function setCountAll(int $countAll){
        $this->countAll = $countAll;
    }

    /**
     * Возвращает limit и offset для запросов getList
     * @return int[]
     */
    public function getNav():array{
        $result = [
            'limit'=>$this->limit,
            'offset'=>$this->offset
        ];
        return $result;
    }

    /**
     * Выводит текущую страницу и общее количество страниц
     * @return array
     */
    public function getListCounter():array{
        $result['CUR_PAGE'] = ($this->offset/$this->limit) + 1;
        $result['PAGE_ALL'] = ceil($this->countAll/$this->limit);
        return $result;
    }
}

?>