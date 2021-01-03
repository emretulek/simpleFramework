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
    protected function print($message, $code, $location = null, $exceptionType = null)
    {
        echo '<style>';
        echo 'body{background-color:#292929}
                .error-container{margin: 15px;background-color: #000000;color: #eee;padding: 5px;font-family: Arial, sans-serif;}
                .error-title{margin: 0;padding: 10px;background-color: #1f1f1f;color: #bb86fc;}.error-type {float: right;}
                .error-message {padding: 15px;}
                .error-footer {background-color: #352c2d;padding: 10px;color: #ff7597;}';
        echo '</style>';
        echo '<div class="error-container">' . PHP_EOL;
        echo '<h3 class="error-title">' . PHP_EOL;
        echo '<small class="error-type">' . $exceptionType . '</small>' . $this->codeToString($code) . PHP_EOL;
        echo '</h3>' . PHP_EOL;
        echo '<div class="error-message">';
        echo '<pre>' . $message . '</pre>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        foreach ($location as $value):
            echo '<div class="error-footer">' . PHP_EOL;
            echo '<b>File:</b> ' . $value['file'] . '&nbsp;&nbsp;' . PHP_EOL;
            echo '<b>Line:</b> ' . $value['line'] . PHP_EOL;
            echo '</div>';
        endforeach;
        echo '</div>';
    }


    /**
     * @param Throwable $e
     */
    public function debug(Throwable $e)
    {
        $exception = $e->getPrevious() ?? $e;
        $exceptionType = get_class($exception);
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $location = $this->getLocation($exception);
        array_unshift($location,['file' => $e->getFile(), 'line' => $e->getLine()]);
        $file = $location[0]['file'];
        $line = $location[0]['line'];

        $console_info = ['code' => $code, 'message' => $message, 'file' => $file, 'line' => $line, 'exception' => $exceptionType];

        switch (config('app.debug')) {
            case 0:
                // kullanıcılara önemli hatalar hakkında bilgi verme.
                if (array_key_exists($code, self::ERROR)) {
                    ob_end_clean();
                    ob_start();
                    $this->print("Something went wrong. You can get detailed information in debug mode.", $code, $location, $exceptionType);
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

        if ($trace) {
            foreach ($trace as $item) {
                if(isset($item['file'], $item['line'])) {
                    $visibleTrace[] = [
                        'file' => $item['file'],
                        'line' => $item['line']
                    ];
                }
            }

            return $visibleTrace;
        }

        return $visibleTrace[] = [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine()
        ];
    }
}
