<?php

namespace Core\Log;



use RuntimeException;

class FileLog Implements LoggerInterface
{

    protected string $file = 'logger.log';

    /**
     * FileLog constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->file = $config['file'];
    }

    /**
     * Log dosyasına yazılacak kayıtlarını formatını belirler
     *
     * @param $message
     * @param $data
     * @param $level
     * @return string
     */
    protected function formatter(string $message, array $data, string $level):string
    {
        $type = "[{$level}]";
        $time = date("d.m.Y H:i:s");
        $data = json_encode($data);

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
    protected function writer($message, $data, $type):bool
    {
        if (is_writable_dir(dirname($this->file))) {

            $handle = fopen($this->file, 'ab+');
            $log = $this->formatter($message, $data, $type);

            if(fwrite($handle, $log, strlen($log))) {
                return fclose($handle);
            }
        }else {
            throw new RuntimeException($this->file . ' dosya yazılabilir değil.', E_WARNING);
        }

        return false;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, string $message, array $context = array())
    {
        $this->writer($message, $context, $level);
    }
}
