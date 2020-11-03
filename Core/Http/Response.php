<?php

namespace Core\Http;


use Core\Exceptions\Exceptions;
use Exception;

class Response
{
    protected array $response = [
        'headers' => [],
        'content' => null,
        'code' => null
    ];

    /**
     * Response constructor.
     * @param null $content
     * @param null|int $code
     * @param null $headers
     */
    public function __construct($content = null, $code = 200, $headers = null)
    {
        $this->content($content);
        $this->code($code);
        $this->headers($headers);

        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function content($content)
    {
        $this->response['content'] = $content;
        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    public function code($code)
    {
        $this->response['code'] = $code;
        return $this;
    }

    /**
     * @param $headers
     * @return $this
     */
    public function headers($headers)
    {
        if (is_array($headers)) {
            foreach ($headers as $type => $value) {
                if (is_integer($type)) {
                    $this->response['headers'][] = $value;
                } else {
                    $this->response['headers'][] = $type . ': ' . $value;
                }
            }
        } elseif($headers) {
            $this->response['headers'][] = $headers;
        }
        return $this;
    }

    /**
     * @param null $options
     */
     public function json($options = null)
    {
        try {
            $this->headers(['Content-Type' => 'application/json']);
            $this->content(json_encode($this->response['content'], $options));
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON decode edilemedi.', E_WARNING);
            }
        } catch (Exception $e) {
            Exceptions::debug($e, 1);
        }
    }


    /**
     * @param null $url
     * @param int $code
     */
    public function redirect($url = null, $code = 302)
    {
        $this->headers(['Location' => $url])->code($code)->send();
    }


    /**
     * @return string
     */
    public function getBody()
    {
        if (is_string($this->response['content']) ||
            is_integer($this->response['content']) ||
            is_float($this->response['content']) ||
            is_bool($this->response['content']) ||
            is_null($this->response['content'])) {

            return (string) $this->response['content'];
        }elseif (is_array($this->response['content']) ||
                is_object($this->response['content'])){

            $this->json();

            return (string) $this->response['content'];

        }else{
            return 'Resource Type: '.get_resource_type ($this->response['content']);
        }
    }

    /**
     * print body
     */
    public function send()
    {
        echo $this->ready();
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ready();
    }

    /**
     * @return string
     */
    private function ready()
    {
        $body = $this->getBody();
        http_response_code($this->response['code']);
        foreach ($this->response['headers'] as $header) {
            header($header);
        }

        return $body;
    }
}
