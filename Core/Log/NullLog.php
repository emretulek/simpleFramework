<?php 
/**
 * @Created 02.01.2021 13:55:02
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class NullLog
 * @package Core\Log
 */


namespace Core\Log;


class NullLog implements LoggerInterface {

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = array())
    {
        // TODO: Implement emergency() method.
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
        // TODO: Implement alert() method.
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
        // TODO: Implement critical() method.
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
        // TODO: Implement error() method.
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
        // TODO: Implement warning() method.
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
        // TODO: Implement notice() method.
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
        // TODO: Implement info() method.
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
        // TODO: Implement debug() method.
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
        // TODO: Implement log() method.
    }
}
