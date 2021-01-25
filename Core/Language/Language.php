<?php

namespace Core\Language;

use Core\App;
use Core\Http\Request;
use Core\Router\Router;
use Core\Session\Session;
use RuntimeException;

class Language
{
    public static bool $isLoaded = false;

    private Request $request;
    private Session $session;
    private Router $router;
    private string $path;
    private array $config;
    private array $default;
    private array $active;

    private array $languages = [];
    private array $translate = [];

    public function __construct(App $app)
    {
        $this->request = $app->resolve(Request::class);
        $this->session = $app->resolve(Session::class);
        $this->router = $app->resolve(Router::class);
        $this->path = $app->basePath . $app->config['path']['language'];
        $this->config = $app->config['app']['language'];
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
        $this->setActive($this->config['key']);

        self::$isLoaded = true;
    }

    /**
     * Aktif dilin adı
     * @return string
     */
    public function getName(): string
    {
        return $this->active['name'];
    }

    /**
     * Aktif dilin anahtarı
     * @return mixed
     */
    public function getKey()
    {
        return $this->active['key'];
    }

    /**
     * Aktif dil locale değeri [TR-tr, EN-en]
     * @return mixed
     */
    public function getLocale()
    {
        return $this->active['locale'];
    }


    /**
     * Ön tanımlı dili ayarlar
     * @param $key
     * @return bool
     */
    public function setDefault($key): bool
    {
        if ($this->exists($key)) {

            $this->default = [
                'key' => $key,
                'name' => $this->languages[$key]['name'],
                'locale' => $this->languages[$key]['locale'],
            ];

            return true;
        }

        return false;
    }


    /**
     * Varsayılan dil
     * @return array
     */
    public function getDefault(): array
    {
        return $this->default;
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

            $this->session->set('_lang', $this->languages[$key]);
            $this->active = $this->languages[$key];
            $this->loadFiles($key);

            return true;
        }

        return false;
    }


    /**
     * @return array [key,name,locale]
     *
     * Aktif dil özelliklerini döndürür
     */
    public function getActive(): array
    {
        return $this->session->get('_lang') ?? $this->default;
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
            return $this->translate[$key][$fileName] = require($file_path);
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
     * @return mixed|array
     */
    public function translate(string $key, ...$args)
    {
        if ($translated = dot_aray_get($this->translate[$this->active['key']], $key)) {
            return is_array($translated) ? $translated : vsprintf($translated, $args);
        }

        if ($default = dot_aray_get($this->translate[$this->default['key']], $key)) {
            return is_array($translated) ? $translated : vsprintf($translated, $args);
        }

        return vsprintf($key, $args);
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
        dot_aray_set($this->translate[$this->active['key']], $key, $value);

        return $value;
    }


    /**
     * Anahtarı girilen dilin kullanılabilir olup olmadığına bakar
     *
     * @param string $lang_key dil anahtarı.
     * @return bool
     */
    public function exists(string $lang_key): bool
    {
        if (array_key_exists($lang_key, $this->languages)) {
            return true;
        }
        return false;
    }

    /**
     * Kullanılabilir dillere yenir bir dil ekler
     * @param string $key
     * @param string $name
     * @param string $locale
     */
    public function add(string $key, string $name, string $locale)
    {
        $this->languages[$key]['key'] = $key;
        $this->languages[$key]['name'] = $name;
        $this->languages[$key]['locale'] = $locale;
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
     * Url üzerinden aktif dili belirler, aktif dil default dil ile aynı ise url yapısında gösterilmez.
     * @TODO site.com/en-us/contact or site.com/en/contact
     */
    public function useUrl()
    {
        $segments = $this->request->segments();

        if (count($segments) > 0) {

            $language = array_shift($segments);

            //default dil ise adres satırında gösterme
            if ($language == $this->default['key']) {
                redirect($this->request->baseUrl() . implode('/', $segments), 301);
                return;
            }

            $this->setActive($language);
        }
    }


    /**
     * Dilin adres satırında gösterilecek formu.
     * Seçili dil default dil ile aynı ise boş dönecektir.
     * @return string
     */
    public function routePrefix(): string
    {
        return $this->active['key'] == $this->default['key'] ? '' : $this->active['key'];
    }
}
