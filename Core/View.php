<?php

namespace Core;


use Core\Config\Config;
use Core\Http\Response;
use Core\Log\LogException;

/**
 * Class View
 * Dosya ve bilgileri düzenleyerek çıktı oluşturur.
 */
class View
{

    protected static $insertData = [];
    protected static $templateName = 'template';
    protected $dynamicPage = 'index';
    protected $data = [];
    protected $buffer = [];


    public function __construct()
    {
        $this->data(self::$insertData);
    }


    /**
     * View sınıfı çağırılmadan data girişi yapar
     *
     * @param array $data
     * @return array
     */
    public static function insertData(array $data)
    {
        return self::$insertData = array_merge(self::$insertData, $data);
    }

    /**
     * page/ dizini altından kullanılacak dosyayı hazırlar.
     *
     * @param string $fileName
     * @param array $data
     * @return View
     */
    public function page(string $fileName, array $data = array())
    {
        $this->data($data);
        $page = ROOT . Config::get('path.page') . '/' . $fileName . EXT;

        try {
            if (file_exists($page)) {
                ob_start();
                extract($this->data);
                include($page);
                $this->buffer[$fileName] = ob_get_clean();
            } else {
                throw new LogException("Sayfa bulunamadı. $page", LogException::TYPE['ERROR']);
            }
        } catch (LogException $exception) {
            $exception->debug();
        }
        return $this;
    }


    /**
     * part/ dizini altından kullanılacak dosyayı hazırlar.
     * 
     * @param string $filePath
     * @param array $data
     * @param string $ext
     * @return $this
     */
    public function path(string $filePath, $data = array(), $ext = EXT)
    {
        $this->data($data);
        $part = ROOT . Config::get('path.view') . '/' . $filePath . $ext;
        try {
            if (file_exists($part)) {
                ob_start();
                extract($this->data);
                include($part);
                $this->buffer[$filePath] = ob_get_clean();
            } else {
                throw new LogException("Sayfa bulunamadı. $part", LogException::TYPE['ERROR']);
            }
        } catch (LogException $exception) {
            $exception->debug();
        }
        return $this;
    }


    /**
     * /page dizininden bir dosyayı template içine dahil ederek hazırlar.
     *
     * @param string $fileName
     * @param array $data
     * @return View
     */
    public function template(string $fileName, $data = array())
    {
        $this->data($data);
        $this->dynamicPage = $fileName;
        $template = ROOT . Config::get('path.template') . '/' . self::$templateName . EXT;

        try {
            if (file_exists($template)) {
                ob_start();
                include($template);
                $this->buffer[] = ob_get_clean();
            } else {
                throw new LogException("Sayfa bulunamadı. $template", LogException::TYPE['ERROR']);
            }
        } catch (LogException $exception) {
            $exception->debug();
        }
        return $this;
    }

    /**
     * Diziyi json header bilgisiyle encode edip buffera alır
     *
     * @param $data
     * @return $this
     */
    public function json($data)
    {
        ob_start();
        (new Response())->json($data)->send();
        $this->buffer[] = ob_get_clean();
        return $this;
    }

    /**
     * Hazırlanan belleği belirtilen header bilgileriyle ekrana basar
     *
     * @param null $code
     * @param null $headers
     * @return $this
     */
    public function render($code = null, $headers = null)
    {
        (new Response($this->getBuffer(), $code, $headers))->send();
        $this->buffer = [];
        return $this;
    }

    /**
     * render etmeden view içeriğini döndürür
     * @return string
     */
    public function getBuffer()
    {
        return implode('', $this->buffer);
    }


    /**
     * Kullanılacak template dosyasını belirler.
     *
     * @param string $template
     * @return View
     */
    public function setTemplate(string $template)
    {
        self::$templateName = $template;
        return $this;
    }

    /**
     * View methodlarına eklenen dataları gerekli tipe dönüştürür
     *
     * @param $data
     * @return $this
     */
    public function data($data)
    {
        if (is_array($data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = array_merge($this->data, compact('data'));
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getBuffer();
    }
}
