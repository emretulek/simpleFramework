<?php

namespace Core\Log;

use Core\Config\Config;
use Core\Http\Request;
use Exception;
use Throwable;


/**
 * Class LogException
 * Aktarılan hataları sınıflandırarak loglar veya debug için ekrana basar.
 */
class LogException extends Exception
{
    const TYPE = [
        'ERROR' => E_ERROR,
        'WARNING' => E_WARNING,
        'PARSE' => E_PARSE,
        'NOTICE' => E_NOTICE,
        'CORE_ERROR' => E_CORE_ERROR,
        'CORE_WARNING' => E_CORE_WARNING,
        'COMPILE_ERROR' => E_COMPILE_ERROR,
        'COMPILE_WARNING' => E_COMPILE_WARNING,
        'CORE_USER_ERROR' => E_USER_ERROR,
        'USER_WARNING' => E_USER_WARNING,
        'USER_NOTICE' => E_USER_NOTICE,
        'STRICT' => E_STRICT,
        'RECOVERABLE_ERROR' => E_RECOVERABLE_ERROR,
        'DEPRECATED' => E_DEPRECATED,
        'USER_DEPRECATED' => E_USER_DEPRECATED
    ];

    protected $messages = [];
    protected $typeName = 'TYPE_LOG';
    protected $logFile = 'errors.log';

    /**
     * LogException constructor.
     * Gelen hata yada bilgileri sınıflandırır
     *
     * @param $message
     * @param int $code
     * @param Throwable|null $previous
     * @param null $file
     * @param null $line
     */
    public function __construct($message = null, int $code = E_NOTICE, Throwable $previous = null, $file = null, $line = null)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        parent::__construct($message, $code, $previous);

        $this->file = $file ? $file : $this->file;
        $this->line = $line ? $line : $this->line;
        $this->previousException();

        $this->typeName = array_search($this->code, self::TYPE);
        $this->logFile = $this->typeName . '.log';
        $this->messages = $message;
    }

    /**
     * Kurucu methodun sınıflandırmasına göre log olarak kayıt eder.
     */
    public function writeLog()
    {
        $line = "[{$this->typeName}]\t[" . date("H:i:s d.m.Y") . "]\t{$this->message}\t{$this->file}\t{$this->line}\t" . Request::forwardedIp() . "\r\n";
        error_log($line, 3, ROOT . Config::get('path.logs') . '/' . $this->logFile);
    }


    /**
     * config/app.config ayarlarına göre hataları kaydeder yada ekrana basar.
     * -1 Tüm hata ve debuglar kapalıdır.
     * 0 Sadece log dosyasına kayıt yapılır.
     * 1 Hatalar javascript konsoluna da yazılır.
     * 2 Hatalar ekrana da yazılır.
     * 3 Hataların debug_back_trace ile ayrıntılı olarak ekrana basılır.
     */
    public function debug()
    {
        if ((int)Config::get('app.debug') > -1) {
            $this->writeLog();
        }
        if ((int)Config::get('app.debug') > 0) {
            console_log(['message' => $this->message, 'file' => $this->file, 'line' => $this->line]);
        }
        if ((int)Config::get('app.debug') > 1) {
            dump('<b>' . $this->typeName . ':</b> ' . $this->message . ' <b>File:</b> ' . $this->file . ' <b>Line:</b> ' . $this->line);
        }
        if ((int)Config::get('app.debug') > 2 and $this->code != self::TYPE['NOTICE']) {
            dump(debug_backtrace());
        }
        if ((int)Config::get('app.debug') < 1 and $this->code == self::TYPE['ERROR']) {
            exit(dump("Something went wrong."));
        }
    }

    /**
     * Exception sınıfına mesaj olarak string değil array verilmişse getMessage yerine getMessages kullanılır
     * @return array|false|mixed|string|null
     */
    public function getMessages()
    {
        return json_decode($this->messages) ? json_decode($this->messages) : $this->messages;
    }

    /**
     * Hata başka bir Excepiton sınıfından devredilmişse, mesaj, dosya ve satır numarasını alır.
     */
    private function previousException()
    {
        if ($exception = $this->getPrevious()) {
            $this->message = $exception->getMessage();
            $this->file = $exception->getFile();
            $this->line = $exception->getLine();
        }
    }
}

