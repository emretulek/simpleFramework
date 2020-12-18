<?php


namespace Core\Csrf;



use Core\App;
use Core\Cookie\Cookie;
use Core\Http\Request;
use Core\Session\Session;

class Csrf
{
    private Request $request;
    private Session $session;
    private Cookie $cookie;

    public function __construct(App $app)
    {
        $this->request = $app->resolve(Request::class);
        $this->session = $app->resolve(Session::class);
        $this->cookie = $app->resolve(Cookie::class);
    }
    /**
     * Csrf için token oluşturup cookie ve sessiona atar
     * $_SESSION old_csrf_token, csrf_token
     * $_COOKIE csrf_token
     */
    public function generateToken():string
    {
        $token = md5(uniqid());
        $this->session->set('old_csrf_token', $this->session->get('csrf_token'));
        $this->session->set('csrf_token', $token);
        $this->cookie->set('csrf_token', $token, 60 * 60, '/', null, false, false);

        return $token;
    }


    /**
     * Input için kullanılacak token değerini döndürür
     * @return bool|string
     */
    public function token()
    {
        return $this->session->get('csrf_token');
    }

    /**
     * bir önceki token değerini döndürür
     * @return bool|string
     */
    public function old_token()
    {
        return $this->session->get('old_csrf_token');
    }

    /**
     * POST veya GET işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function check():bool
    {
        if ($this->request->request('csrf_token') == $this->old_token() && $this->isSelfReferer()) {
            return false;
        }

        return true;
    }

    /**
     * POST işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function checkPost():bool
    {
        if($this->request->method('post')) {
            if ($this->request->post('csrf_token') == $this->old_token() && $this->isSelfReferer()) {
                return false;
            }
        }

        return true;
    }


    /**
     * GET işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function checkGet():bool
    {
        if($this->request->method('get')) {
            if ($this->request->post('csrf_token') == $this->old_token() && $this->isSelfReferer()) {
                return false;
            }
        }

        return true;
    }


    /**
     * GET, POST işlemlerinde Cookie üzerinden kontrol eder CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function checkCookie():bool
    {
        if($this->cookie->get('csrf_token') == $this->old_token() && $this->isSelfReferer()){
            if($this->request->server('cache-control') != 'max-age=0') {
                return false;
            }
        }

        return true;
    }


    /**
     * istek kaynağı kendi sitemiz ise true
     * @return bool
     */
    private function isSelfReferer():bool
    {
        if(strpos($this->request->referer(), $this->request->host())){
            return true;
        }

        return false;
    }
}
