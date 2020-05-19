<?php

namespace Core;


use Core\Config\Config;
use Core\Exceptions\Exceptions;
use Core\Http\Response;
use Exception;

/**
 * Class View
 * Dosya ve bilgileri düzenleyerek çıktı oluşturur.
 */
class View
{

    protected static $insertData = [];
    protected static $templateName = 'default';
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
     * @param string $__fileName
     * @param array $__data
     * @return View
     */
    public function page(string $__fileName, array $__data = array())
    {
        $this->data($__data);
        $__page = ROOT . Config::get('path.page') . '/' . $__fileName . EXT;

        try {
            if (file_exists($__page)) {
                ob_start();
                extract($this->data);
                include($__page);
                $this->buffer[$__fileName] = ob_get_clean();
            } else {
                throw new Exception("Sayfa bulunamadı. $__page", E_ERROR);
            }
        } catch (Exception $__e) {
            Exceptions::debug($__e);
        }
        return $this;
    }


    /**
     * part/ dizini altından kullanılacak dosyayı hazırlar.
     * 
     * @param string $__filePath
     * @param array $__data
     * @param string $__ext
     * @return $this
     */
    public function path(string $__filePath, $__data = array(), $__ext = EXT)
    {
        $this->data($__data);
        $__part = ROOT . Config::get('path.view') . '/' . $__filePath . $__ext;
        try {
            if (file_exists($__part)) {
                ob_start();
                extract($this->data);
                include($__part);
                $this->buffer[$__filePath] = ob_get_clean();
            } else {
                throw new Exception("Sayfa bulunamadı. $__part", E_ERROR);
            }
        } catch (Exception $__e) {
            Exceptions::debug($__e);
        }
        return $this;
    }


    /**
     * /page dizininden bir dosyayı template içine dahil ederek hazırlar.
     *
     * @param string $__fileName
     * @param array $__data
     * @return View
     */
    public function template(string $__fileName, $__data = array())
    {
        $this->data($__data);
        $this->dynamicPage = $__fileName;
        $__template = ROOT . Config::get('path.template') . '/' . self::$templateName . EXT;

        try {
            if (file_exists($__template)) {
                ob_start();
                include($__template);
                $this->buffer[] = ob_get_clean();
            } else {
                throw new Exception("Sayfa bulunamadı. $__template", E_ERROR);
            }
        } catch (Exception $__e) {
            Exceptions::debug($__e);
        }
        return $this;
    }

    /**
     * Diziyi json header bilgisiyle encode edip buffera alır
     *
     * @param $__data
     * @return $this
     */
    public function json($__data)
    {
        ob_start();
        (new Response())->json($__data)->send();
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
    private function data($data)
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
