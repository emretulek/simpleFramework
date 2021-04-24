<?php

namespace Core\View;

use Core\App;
use Core\Http\Response;
use Exception;

/**
 * Class View
 * Dosya ve bilgileri düzenleyerek çıktı oluşturur.
 */
class View
{
    private App $app;

    protected string $layoutName = 'default';
    protected string $dynamicPage = 'index';

    protected static array $insertedData = [];
    protected array $data = [];
    protected array $buffer = [];


    /**
     * View constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }


    /**
     * View sınıfı çağırılmadan data girişi yapar
     *
     * @param array $data
     * @return array
     */
    public static function insertData(array $data): array
    {
        return static::$insertedData = array_merge(static::$insertedData, $data);
    }

    /**
     * page/ dizini altından kullanılacak dosyayı hazırlar.
     *
     * @param string $fileName
     * @param array $data
     * @return View
     */
    public function page(string $fileName, array $data = array()): self
    {
        $this->data($data);
        $___page = $this->app->basePath . $this->app->config['path']['page'] . '/' . $fileName . EXT;

        try {
            if (is_readable_file($___page)) {
                ob_start();
                extract($this->data);
                require($___page);
                $this->buffer[$fileName] = ob_get_clean();
            } else {
                throw new Exception("Sayfa bulunamadı. $___page", E_ERROR);
            }
        } catch (Exception $e) {
            $this->app->debug($e);
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
    public function path(string $filePath, $data = array(), $ext = EXT): self
    {
        $this->data($data);
        $___part = $this->app->basePath . $this->app->config['path']['view'] . '/' . $filePath . $ext;
        try {
            if (is_readable_file($___part)) {
                ob_start();
                extract($this->data);
                require($___part);
                $this->buffer[$filePath] = ob_get_clean();
            } else {
                throw new Exception("Sayfa bulunamadı. $___part", E_ERROR);
            }
        } catch (Exception $e) {
            $this->app->debug($e);
        }
        return $this;
    }


    /**
     * /page dizininden bir dosyayı layout içine dahil ederek hazırlar.
     *
     * @param string $fileName
     * @param array $data
     * @return View
     */
    public function layout(string $fileName, $data = array()): self
    {
        $this->data($data);
        $this->dynamicPage = $fileName;
        $___layout = $this->app->basePath . $this->app->config['path']['layout'] . '/' . $this->layoutName . EXT;

        try {
            if (is_readable_file($___layout)) {
                ob_start();
                extract($this->data);
                require($___layout);
                $this->buffer[] = ob_get_clean();
            } else {
                throw new Exception("Sayfa bulunamadı. $___layout", E_ERROR);
            }
        } catch (Exception $e) {
            $this->app->debug($e);
        }
        return $this;
    }


    /**
     * Layout methoduna aktarılan dinamik sayfa
     * @return $this
     */
    public function getLayoutPage(): self
    {
        return $this->page($this->dynamicPage);
    }

    /**
     * Diziyi json header bilgisiyle encode edip buffera alır
     *
     * @param $data
     * @return Response
     */
    public function json($data): Response
    {
        return $this->response()->content($data)->toJson();
    }

    /**
     * Hazırlanan belleği belirtilen header bilgileriyle ekrana basar
     *
     * @param int $code
     * @param array $headers
     */
    public function render(int $code = 200, array $headers = [])
    {
        $this->response($code, $headers)->send();
        $this->buffer = [];
    }

    /**
     * render etmeden view içeriğini döndürür
     * @param bool $clear
     * @return string
     */
    public function getBuffer($clear = true): string
    {
        $buffer = implode(PHP_EOL, $this->buffer);

        if($clear === true){
            $this->buffer = [];
        }
        return $buffer;
    }


    /**
     * Kullanılacak layout dosyasını belirler.
     *
     * @param string $layout
     * @return View
     */
    public function setLayout(string $layout): self
    {
        $this->layoutName = $layout;
        return $this;
    }

    /**
     * View methodlarına eklenen dataları gerekli tipe dönüştürür
     *
     * @param $data
     * @return $this
     */
    private function data($data): self
    {
        $this->data = array_merge($this->data, static::$insertedData);
        static::$insertedData = [];

        if (is_array($data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = array_merge($this->data, compact('data'));
        }
        return $this;
    }

    /**
     * @param int $code
     * @param array $headers
     * @return Response
     */
    public function response(int $code = 200, array $headers = []):Response
    {
        return $this->app->resolve(Response::class)
            ->content($this->getBuffer(false))->code($code)->headers($headers);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getBuffer();
    }
}
