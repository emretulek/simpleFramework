<?php

namespace Core\Language;

use Core\App;
use Core\Cookie\Cookie;
use Core\Http\Request;
use Core\Session\Session;
use RuntimeException;

class Language
{
    public string $routePrefix = '';

    private App $app;
    private string $path;
    private array $config;
    private string $default;
    private string $active;

    private string $useSession = '';
    private string $useCookie = '';

    private array $languages = [];
    private array $translate = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->path = $app->basePath . $app->config['path']['language'];
        $this->config = $app->config['app']['language'];

        $this->load();
    }


    /**
     * Uygulamanın dil desteğini aktif eder
     */
    public function load()
    {
        $this->add(
            $this->config['key'],
            $this->config['name'],
            $this->config['locale']
        );

        $this->setDefault($this->config['key']);
    }


    /**
     * Ön tanımlı dili ayarlar
     * @param $key
     * @return bool
     */
    public function setDefault($key): bool
    {
        if ($this->exists($key)) {

            $this->default = $key;
            $this->active = $key;
            $this->loadFiles($key);

            return true;
        }

        return false;
    }


    /**
     * Varsayılan dil anahtarı
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }


    /**
     * Aktif dilin adı
     * @return string
     */
    public function getName(): string
    {
        return $this->languages[$this->getActive()]['name'] ?? $this->languages[$this->default]['name'];
    }

    /**
     * Aktif dilin anahtarı
     * @return mixed
     */
    public function getKey()
    {
        return $this->languages[$this->getActive()]['key'] ?? $this->languages[$this->default]['key'];
    }

    /**
     * Aktif dil locale değeri [TR-tr, EN-en]
     * @return mixed
     */
    public function getLocale()
    {
        return $this->languages[$this->getActive()]['locale'] ?? $this->languages[$this->default]['locale'];
    }


    /**
     * Aktif dili belirler
     *
     * @param string $key
     * @return bool
     */
    public function setActive(string $key): bool
    {
        if ($this->exists($key)) {

            $this->active = $key;

            if($this->useSession) {
                $this->session()->set($this->useSession, $key);
            }

            if($this->useCookie) {
                $this->cookie()->set($this->useCookie, $key);
            }

            $this->loadFiles($key);
            return true;
        }

        return false;
    }


    /**
     * @return string
     *
     * Aktif dil anahtaru
     */
    public function getActive(): string
    {
        if($this->useCookie){
            $this->active = (string) $this->cookie()->get($this->useCookie) ?: $this->active;
        }

        if($this->useSession){
            $this->active = (string) $this->session()->get($this->useSession) ?: $this->active;
        }

        return $this->active;
    }


    /**
     * Farklı bir konumdan yeni bir dil dosyası ekler
     *
     * @param string $key dosyanın kullanılacağı dil anahtarı.
     * @param string $file_path yüklenecek dosya yolu
     * @return bool|array
     */
    public function addFile(string $key, string $file_path)
    {
        if (is_readable_file($file_path)) {
            $fileName = pathinfo($file_path, PATHINFO_FILENAME);
            $fileContent = require($file_path);
            return $this->translate[$key][$fileName] = config_parser($fileContent);
        }

        throw new RuntimeException('Dil dosyası bulunamadı. ' . $file_path, E_NOTICE);
    }


    /**
     * lang dizini altındaki ilgili dil dosyalarını yükler
     *
     * @param $key
     */
    private function loadFiles($key)
    {
        $fullPath = $this->path . '/' . $key;

        if (is_readable_dir($fullPath)) {
            $files = array_diff(scandir($fullPath), ['..', '.']);

            foreach ($files as $file) {
                $this->addFile($key, $fullPath . '/' . $file);
            }
        }
    }


    /**
     * Indexi girilen çeviriyi döndürür
     *
     * @param string $key dosya ismi ile birlikte dizi indexi örn; {lang/tr/home.php, $title} için {home.title}
     * @param mixed ...$args
     * @return array|string
     */
    public function translate(string $key, ...$args)
    {
        if(array_key_exists($this->getKey(), $this->translate)) {
            if ($translated = dot_aray_get($this->translate[$this->getKey()], $key)) {
                return is_array($translated) ? $translated : vsprintf(translate_parser($translated, ...$args), $args);
            }
        }
        if(array_key_exists($this->default, $this->translate)) {
            if ($translated = dot_aray_get($this->translate[$this->default], $key)) {
                return is_array($translated) ? $translated : vsprintf(translate_parser($translated, ...$args), $args);
            }
        }

        return vsprintf(translate_parser($key, ...$args), $args);
    }


    /**
     * Dile yeni çeviriler eklemek için kullanılır
     *
     * @param string $key nokta ile birleştirilmiş dizi indexleri
     * @param mixed $value çeviri
     * @return void
     */
    public function addTranslate(string $key, $value)
    {
        dot_aray_set($this->translate[$this->getKey()], $key, $value);

        return $value;
    }


    /**
     * Anahtarı girilen dilin kullanılabilir olup olmadığına bakar
     *
     * @param string $key dil anahtarı.
     * @return bool
     */
    public function exists(string $key): bool
    {
        if (array_key_exists($key, $this->languages)) {
            return true;
        }
        return false;
    }

    /**
     * Kullanılabilir dillere yenir bir dil ekler
     * @param string $key
     * @param string $name
     * @param ?string $locale
     */
    public function add(string $key, string $name, string $locale = null)
    {
        $this->languages[$key]['key'] = $key;
        $this->languages[$key]['name'] = $name;
        $this->languages[$key]['locale'] = $locale ?? $this->config['locale'];
    }

    /**
     * Kullanılabilir dillerden keyi girileni kaldırır
     * @param string $lang_key
     */
    public function remove(string $lang_key)
    {
        unset($this->languages[$lang_key]);
    }

    /**
     * Yüklü dil listesi
     * @return array
     */
    public function list():array
    {
        return $this->languages;
    }


    /**
     * @param string $session_name
     * @return $this
     */
    public function useSession(string $session_name):Language
    {
        $this->useSession = $session_name;
        return $this;
    }

    /**
     * @param string $cookie_name
     * @return $this
     */
    public function useCookie(string $cookie_name):Language
    {
        $this->useCookie = $cookie_name;
        return $this;
    }

    /**
     * Dilin adres satırında gösterilecek formu.
     * Seçili dil default dil ile aynı ise boş dönecektir.
     * @param int $segmentNum
     * @return string
     */
    public function routePrefix(int $segmentNum = 0): string
    {
        $segments = $this->request()->segments();

        if (count($segments) > $segmentNum) {

            $language = current(array_slice($segments, $segmentNum, 1));
            unset($segments[$segmentNum]);

            //default dil ise adres satırında gösterme
            if ($language == $this->default) {
                redirect($this->request()->baseUrl() . implode('/', $segments), 301);
                return '';
            }

            if($this->exists($language)) {
                $this->routePrefix = $language;
                $this->setActive($language);
            }

            return $this->getKey() == $this->default ? '' : $this->getKey();
        }

        $this->setActive($this->default);
        return '';
    }

    /**
     * @param string $prefix
     */
    public function setRoutePrefix(string $prefix):void
    {
        $this->routePrefix = $prefix;
    }

    /**
     * @return string
     */
    public function getRoutePrefix():string
    {
        return $this->routePrefix;
    }

    /**
     * @return Session
     */
    private function session():Session
    {
        return $this->app->resolve(Session::class);
    }

    /**
     * @return Cookie
     */
    private function cookie():Cookie
    {
        return $this->app->resolve(Cookie::class);
    }

    /**
     * @return Request
     */
    private function request():Request
    {
        return $this->app->resolve(Request::class);
    }
}
