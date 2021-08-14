<?php


namespace LibSite;


/**
 * Класс для работы с csv-файлами
 * @author дизайн студия ox2.ru
 */
class CSV {

    private $_csv_file = null;

    /**
     * @param string $csv_file  - путь до csv-файла
     */
    public function __construct($csv_file) {
        if (file_exists($csv_file)) { //Если файл существует
            $this->_csv_file = $csv_file; //Записываем путь к файлу в переменную
        }
        else { //Если файл не найден то вызываем исключение
            throw new Exception("Файл ".$csv_file." не найден");
        }
    }

    public function setCSV(Array $csv) {
        //Открываем csv для до-записи,
        //если указать w, то  ифнормация которая была в csv будет затерта
        $handle = fopen($this->_csv_file, "a");

        foreach ($csv as $value) { //Проходим массив
            //Записываем, 3-ий параметр - разделитель поля
            fputcsv($handle, explode(";", $value), ";");
        }
        fclose($handle); //Закрываем
    }

    /**
     * Метод для чтения из csv-файла. Возвращает массив с данными из csv
     * @return array;
     */
    public function getCSV() {
        $handle = fopen($this->_csv_file, "r"); //Открываем csv для чтения

        $array_line_full = array(); //Массив будет хранить данные из csv
        //Проходим весь csv-файл, и читаем построчно. 3-ий параметр разделитель поля
        while (($line = fgetcsv($handle, 0, ";")) !== FALSE) {
            $array_line_full[] = $line; //Записываем строчки в массив
        }
        fclose($handle); //Закрываем файл
        return $array_line_full; //Возвращаем прочтенные данные
    }

}
/*
 * Пример вызова
try {
    $csv = new CSV($_SERVER['DOCUMENT_ROOT']."/test.csv"); //Открываем наш csv

    echo "<h2>CSV до записи:</h2>";
    $get_csv = $csv->getCSV();
    foreach ($get_csv as $value) { //Проходим по строкам
        echo "Имя: " . $value[0] . "<br/>";
        echo "Должность: " . $value[1] . "<br/>";
        echo "Телефон: " . $value[2] . "<br/>";
        echo "--------<br/>";
    }


      //Запись новой информации в CSV

    $arr = array("Антонов Б.А.;Админ OX2.ru",
        "Колобков В.Б.;Босс OX2.ru");
    $csv->setCSV($arr);
}
catch (Exception $e) { //Если csv файл не существует, выводим сообщение
    echo "Ошибка: " . $e->getMessage();
}
*/