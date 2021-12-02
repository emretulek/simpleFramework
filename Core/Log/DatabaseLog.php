<?php

namespace Core\Log;

use Core\Database\Database;
use Core\Database\QueryBuilder;
use Core\Era\Era;
use Exception;
use RuntimeException;

class DatabaseLog implements LoggerInterface
{
    protected string $table = 'logger';
    protected Database $database;

    /**
     * DatabaseLog constructor.
     * @param Database $database
     * @param array $config
     */
    public function __construct(Database $database, array $config)
    {
        $this->table = $config['table'];
        $this->database = $database;
    }

    /**
     * @return QueryBuilder
     */
    protected function table(): QueryBuilder
    {
        return $this->database->table($this->table);
    }

    /**
     * Log yazma işlemini gerçekleştirir
     *
     * @param string $message
     * @param array $data
     * @param string $level
     * @return bool
     */
    protected function writer(string $message, array $data, string $level): bool
    {
        try {
            $data = json_encode($data);
            return (bool)$this->table()->insert([
                'message' => $message,
                'data' => $data,
                'level' => $level,
                'time' => Era::now()
            ]);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), E_WARNING);
        }
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
