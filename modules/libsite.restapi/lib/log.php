<?php

namespace LibSite\RestApi;

class Log
{
    public $fileLog;
    protected $directory;
    public function __construct($dirLog, $fileNameLog = 'log')
    {
        $this->fileLog = $fileNameLog;
        $this->directory = $dirLog;
    }

    /**
     * Запись в файл лога
     * @param string $title
     * @param string $message
     */
    public function addLog(string $title, string $message=''){
        if(!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
        $str = date("d-m-Y H:i:s") . ' ' . $title. ' - '. $message."\r\n";
        file_put_contents($this->directory.$this->fileLog.'_'.date("d_m_Y").'.log', $str, FILE_APPEND);
    }

    /**
     * Удаление старых файлов в разделе
     * @param $expire_time - время в сек от даты изменения после которого файл будет удален
     * @return void
     */
    public function clearOldFile($expire_time=1209600)
    {
        if (is_dir($this->directory)) {
            if ($dh = opendir($this->directory)) {
                while (($file = readdir($dh)) !== false) {
                    $time_sec=time();
                    $time_file=filemtime($this->directory . $file);
                    $time=$time_sec-$time_file;
                    $unlink =$this->directory.$file;
                    if (is_file($unlink)){
                        if ($time>$expire_time){
                            unlink($unlink);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }


}