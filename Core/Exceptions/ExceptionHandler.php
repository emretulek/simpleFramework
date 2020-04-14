<?php


namespace Core\Exceptions;

use Core\Config\Config;
use Core\Http\Request;
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


    /**
     * ExceptionHandler constructor.
     */
    public function bootstrap()
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
     * @throws ErrorException
     */
    public function handleError($code, $message, $file = '', $line = 0)
    {
        if (error_reporting() & $code) {
            throw new ErrorException($message, $code, 1, $file, $line);
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
            try{
                throw new ErrorException($error['message'], $error['type'], 1, $error['file'], $error['line']);
            }catch (Exception $e){
                $this->handleException($e);
            }
        }
    }

    /**
     * @param $code
     * @return int|mixed
     */
    public function codeToString($code)
    {
        $codes = self::ERROR + self::WARNING + self::NOTICE;

        if(array_key_exists($code, $codes)){
            return $codes[$code];
        }

        return 0;
    }

    /**
     * @param $message
     * @param $code
     * @param null $file
     * @param null $line
     */
    public function print($message, $code, $file = null, $line = null)
    {
        echo '<div style="margin: 5px 15px; border: solid 1px #cecece">' . PHP_EOL;
        echo '<h3 style="background-color: #cecece; padding: 10px; margin: 0">' . $this->codeToString($code) . '</h3>' . PHP_EOL;
        echo '<div style="background-color: #eeeeee; color:#a94442; padding: 10px;">';
        echo '<pre>' . $message . '</pre>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        echo '<div style="background-color: #cecece; padding: 5px">' . PHP_EOL;
        echo '<b>File:</b> ' . $file . '&nbsp;&nbsp;' . PHP_EOL;
        echo '<b>Line:</b> ' . $line . PHP_EOL;
        echo '</div>';
        echo '</div>';
    }


    /**
     * @param Throwable $e
     * @param int $backtrace hata dosya ve satırnı değil çağıran fonksiyona ait dosya ve satırı döndürür girilen sayı ne kadar geri gidileceğini belirtir.
     */
    public function debug(Throwable $e, int $backtrace = 0)
    {
        $console_info = ['code'=> $this->codeToString($e->getCode()),'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()];
        $code = $e->getCode();
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $backtrace ? $e->getTrace()[$backtrace] : null;

        switch (Config::get('app.debug')){
            case 0:
                // kullanıcılara önemli hatalar hakkında bilgi verme.
                if(array_key_exists($code, self::ERROR)) {
                    ob_end_clean();
                    $this->print("Yolunda gitmeyen birşeyler var. Debug modunda ayrıntılı bilgi edinebilirsiniz.", $code, $file, $line);
                    exit();
                }
                if(!array_key_exists($code, self::NOTICE)) {
                    $trace ? $this->writeLog($message, $code, $trace['file'], $trace['line']):$this->writeLog($message, $code, $file, $line);
                }
                break;
            case 1:
                if(!array_key_exists($code, self::NOTICE)) {
                    console_log($console_info);
                    $trace ? $this->writeLog($message, $code, $trace['file'], $trace['line']):$this->writeLog($message, $code, $file, $line);
                }
                break;
            case 2:
                console_log($console_info);
                $trace ? $this->print($message, $code, $trace['file'], $trace['line']):$this->print($message, $code, $file, $line);
                $trace ? $this->writeLog($message, $code, $trace['file'], $trace['line']):$this->writeLog($message, $code, $file, $line);
                break;
            case 3:
                console_log($console_info);
                $this->print($message, $code, $file, $line);
                dump($e->getTrace());
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

        $error_message = "[{$this->codeToString($code)}]\t[" . date("H:i:s d.m.Y") . "]\t{$message}\t{$file}\t{$line}\t" . Request::forwardedIp() . PHP_EOL;
        error_log($error_message, 3, ROOT . Config::get('path.logs') . '/' . $this->codeToString($code).'.log');
    }
}
