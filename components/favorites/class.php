<?php


class FourPx extends \CBitrixComponent {

    /**
     * подготавливаем параметры
     *
     * @param $arParams
     * @return mixed
     */
    public function onPrepareComponentParams ($arParams)
    {
        $arParams['LIST_NAME'] = $arParams['LIST_NAME'] ? : 'FAV_LIST';
        $arParams['FILTER_NAME'] = $arParams['FILTER_NAME'] ? : 'arrFilter';
        return $arParams;
    }

    /**
     * возвращает количество элементов в списке
     * @return int
     */
    public function getCount() {
        return count($_SESSION[$this->arParams['LIST_NAME']]['ITEMS']);
    }

    /**
     * Добавляет элемент в список
     *
     * @param $itemId
     */
    public function add($itemId) {
        if(!is_array($_SESSION[$this->arParams['LIST_NAME']]['ITEMS'])) {
            $_SESSION[$this->arParams['LIST_NAME']]['ITEMS'] = array();
        }
        $_SESSION[$this->arParams['LIST_NAME']]['ITEMS'][] = $itemId;
    }

    /**
     * удаляет элемент из списка
     *
     * @param $itemId
     */
    public function delete($itemId) {
        foreach ($_SESSION[$this->arParams['LIST_NAME']]['ITEMS'] as $index => $id) {
            if ($id == $itemId) {
                unset($_SESSION[$this->arParams['LIST_NAME']]['ITEMS'][$index]);
                break;
            }
        }
    }

    /**
     * очищает список избранного
     */
    public function clear() {
        $_SESSION[$this->arParams['LIST_NAME']]['ITEMS'] = array();
    }

    /**
     * запуск компонента
     */
    public function executeComponent ()
    {
        global $APPLICATION;
        $action = $this->arParams['ACTION'];
        $id = $_REQUEST['id'];
        $ajax = $_REQUEST['ajax'] == 'Y';
        switch ($action) {
            case 'delete':
                    $this->delete($id);
                    if ($ajax) {
                        $APPLICATION->RestartBuffer();
                        print json_encode(array('STATUS' => 'OK', 'MESSAGE' => 'Товар удален из списка избранного'));
                        exit;
                    }
                break;
            case 'add':
                $this->add($id);
                if ($ajax) {
                    $APPLICATION->RestartBuffer();
                    print json_encode(array('STATUS' => 'OK', 'MESSAGE' => 'Товар добавлен в список избранного'));
                    exit;
                }
                break;
            case 'clear':
                $this->clear();
                if ($ajax) {
                    $APPLICATION->RestartBuffer();
                    print json_encode(array('STATUS' => 'OK', 'MESSAGE' => 'Список избранного очищен'));
                    exit;
                }
                break;
            case 'count':
                $this->arResult['COUNT'] = $this->getCount();
                $this->includeComponentTemplate();
                break;
            default:
                $filterName = $this->arParams['FILTER_NAME'];
                $GLOBALS[$filterName] = array(
                    'ID' => $_SESSION[$this->arParams['LIST_NAME']]['ITEMS']
                );
                break;

        }

    }

}

