<?php

namespace Core\Log;


use Core\Config\Config;

class FileLogWriter Implements LogWriterInterface
{

    protected $file = '/logger.log';

    /**
     * FileLogWriter constructor.
     */
    public function __construct()
    {
        $this->file = ROOT.Config::get('path.logs').$this->file;
    }

    /**
     * Log dosyasına yazılacak kayıtlarını formatını belirler
     *
     * @param $message
     * @param $data
     * @param $type
     * @param $time
     * @return string
     */
    protected function formatter($message, $data, $type, $time)
    {
        $type = "[{$type}]";
        $time = date("d.m.Y H:i:s", $time);
        $data = is_array($data) || is_object($data) ? serialize($data) : $data;

        return sprintf("%s\t%s\t%s\t%s".PHP_EOL, $time, $type, $message, $data);
    }

    /**
     * Log kayıtlarını belirtilen dosyaya yazar
     *
     * @param $message
     * @param $data
     * @param $type
     * @return bool
     */
    public function writer($message, $data, $type)
    {
        try {
            if (is_file($this->file)) {

                $handle = fopen($this->file, 'ab+');
                $log = $this->formatter($message, $data, $type, time());

                if (is_writable($this->file)) {
                    if(fwrite($handle, $log, strlen($log))) {
                        return fclose($handle);
                    }
                }else {
                    throw new LogException($this->file . ' dosya yazılabilir değil.', LogException::TYPE['WARNING']);
                }
            } else {
                throw new LogException($this->file . ' dosya bulunamadı.', LogException::TYPE['WARNING']);
            }
        }catch (LogException $exception){
            $exception->debug();
        }

        return false;
    }
}
