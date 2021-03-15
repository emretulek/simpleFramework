<?php

namespace Core\Exceptions;

use Core\Http\Response;
use Exception;
use ErrorException;
use Throwable;

class ExceptionHandler
{
    const ERROR = [
        0 => 'ERROR',
        E_ERROR => 'ERROR',
        E_CORE_ERROR => 'ERROR',
        E_COMPILE_ERROR => 'ERROR',
        E_USER_ERROR => 'ERROR',
        E_RECOVERABLE_ERROR => 'ERROR',
        E_PARSE => 'PARSE',
    ];

    const WARNING = [
        E_WARNING => 'WARNING',
        E_CORE_WARNING => 'WARNING',
        E_COMPILE_WARNING => 'WARNING',
        E_USER_WARNING => 'WARNING',
    ];

    const NOTICE = [
        E_NOTICE => 'NOTICE',
        E_USER_NOTICE => 'NOTICE',
        E_STRICT => 'STRICT',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'DEPRECATED'
    ];


    public function __construct()
    {
        $this->bootstrap();
    }

    /**
     * ExceptionHandler constructor.
     */
    protected function bootstrap()
    {
        error_reporting(-1);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

        ini_set('display_errors', 'Off');

    }

    /**
     * @param $code
     * @param $message
     * @param string $file
     * @param int $line
     */
    public function handleError($code, $message, $file = '', $line = 0)
    {
        try {
            throw new ErrorException($message, $code, 1, $file, $line);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }


    /**
     * @param Throwable $e
     */
    public function handleException(Throwable $e)
    {
        $this->debug($e);
    }


    /**
     * betik sonlandığında sebep olan hata varsa exception olarak değerlendirilir
     */
    public function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && array_key_exists($error['type'], self::ERROR)) {
            try {
                throw new ErrorException($error['message'], $error['type'], 1, $error['file'], $error['line']);
            } catch (Exception $e) {
                $this->handleException($e);
            }
        }
    }

    /**
     * @param $code
     * @return int|mixed
     */
    protected function codeToString($code)
    {
        $codes = self::ERROR + self::WARNING + self::NOTICE;

        if (array_key_exists($code, $codes)) {
            return $codes[$code];
        }

        return 0;
    }

    /**
     * @param $message
     * @param $code
     * @param null $location
     * @param null $exceptionType
     */
    protected function print($message, $code, $location = [], $exceptionType = null)
    {
        echo '<style>';
        echo '  .error-container{margin:15px;background-color:#ffffff;color:#6a34bf;padding:5px;font-family: sans-serif;
        box-shadow:0 2px 5px #00000045;overflow-y:auto;position:relative;z-index:9999}
                .error-title{margin:0;padding:10px;background-color:#692ebc;color:#ffffff}
                .error-type{float:right;color:#cab8e9}
                .error-message{margin:5px 0;padding:15px;color:#6a34bf;box-shadow:0 0 5px #00000045 inset}
                .error-footer{background-color:#eeeeee;padding:10px;color:#ff7597;display:flex;justify-content:space-between;margin:0 0 5px}';
        echo '</style>';
        echo '<div class="error-container">' . PHP_EOL;
        echo '<h3 class="error-title">' . PHP_EOL;
        echo '<small class="error-type">' . $exceptionType . '</small>' . $this->codeToString($code) . PHP_EOL;
        echo '</h3>' . PHP_EOL;
        echo '<div class="error-message">'.$message.'</div>' . PHP_EOL;
        foreach ($location as $value):
            echo '<pre class="error-footer">' . PHP_EOL;
            echo '<span>File: ' . $value['file'] . '</span> ';
            echo '<span>Line: ' . $value['line'] . '</span> ' . PHP_EOL;
            echo '</pre>';
        endforeach;
        echo '</div>';
    }


    /**
     * @param Throwable $e
     */
    public function debug(Throwable $e)
    {
        $exception = $e->getPrevious() ?? $e;
        $exceptionType = get_class($e);
        $code = $e->getCode();
        $message = $exception->getMessage();
        $location = $this->getLocation($exception);
        $firstCatchLocation[] = ['file' => $e->getFile(), 'line' => $e->getLine()];
        array_splice( $location, 1, 0, $firstCatchLocation);
        $file = $location[0]['file'];
        $line = $location[0]['line'];
        $console_info = ['code' => $code, 'message' => $message, 'file' => $file, 'line' => $line, 'exception' => $exceptionType];

        switch (config('app.debug')) {
            case 0:
                // kullanıcılara önemli hatalar hakkında bilgi verme.
                if (array_key_exists($code, self::ERROR)) {
                    ob_end_clean();
                    ob_start();
                    $this->print("Something went wrong. You can get detailed information in debug mode.", $code);
                    $content = ob_get_clean();
                    echo new Response($content, 500);
                    exit;
                }
                if (!array_key_exists($code, self::NOTICE)) {
                    $this->writeLog($message, $code, $file, $line);
                }
                break;
            case 1:
                if (!array_key_exists($code, self::NOTICE)) {
                    console_log($console_info);
                    $this->writeLog($message, $code, $file, $line);
                }
                break;
            case 2:
                console_log($console_info);
                $this->print($message, $code, $location, $exceptionType);
                $this->writeLog($message, $code, $file, $line);
                break;
            case 3:
                console_log($console_info);
                $this->print($message, $code, $location, $exceptionType);
                dump($exception);
                $this->writeLog($message, $code, $file, $line);
                break;
            default:
                $this->writeLog($message, $code, $file, $line);
        }
    }


    /**
     * @param $message
     * @param $code
     * @param null $file
     * @param null $line
     */
    public function writeLog($message, $code, $file = null, $line = null)
    {
        $error_message = "[{$this->codeToString($code)}]\t[" . date("H:i:s d.m.Y") . "]\t{$message}\t{$file}\t{$line}\t" .
            $_SERVER['REMOTE_ADDR'] . "\t" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . PHP_EOL;
        error_log($error_message, 3, ROOT . '/' . config('path.error_log') . '/' . $this->codeToString($code) . '.log');
    }


    /**
     * @param Throwable $throwable
     * @return array
     */
    private function getLocation(Throwable $throwable): array
    {
        $trace = $throwable->getTrace();
        $visibleTrace = [];

        $visibleTrace[] = [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine()
        ];

        if ($trace) {
            foreach ($trace as $item) {
                if (isset($item['file'], $item['line'])) {
                    $visibleTrace[] = [
                        'file' => $item['file'],
                        'line' => $item['line']
                    ];
                }
            }
        }

        return $visibleTrace;
    }
}
