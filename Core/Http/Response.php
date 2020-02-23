<?php

namespace Core\Http;


use Core\Exceptions\Exceptions;
use Exception;

class Response
{
    protected $response = [
        'headers' => [],
        'content' => null,
        'code' => null
    ];

    public function __construct($content = null, $code = null, $headers = null)
    {
        $this->content($content);
        $this->code($code);
        $this->headers($headers);

        return $this;
    }

    public function content($content)
    {
        $this->response['content'] = $content;
        return $this;
    }

    public function code($code)
    {
        $this->response['code'] = $code;
        return $this;
    }

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
        } else {
            $this->response['headers'][] = $headers;
        }
        return $this;
    }

    public function json($array)
    {
        try {
            $this->headers(['Content-Type' => 'application/json']);
            $this->content(json_encode($array, JSON_FORCE_OBJECT));
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON decode edilemedi.', E_WARNING);
            }
        } catch (Exception $e) {
            Exceptions::debug($e);
        }
        return $this;
    }


    public function redirect($url = null, $code = 302)
    {
        $this->headers(['Location' => $url])->code($code)->send();
        return $this;
    }


    public function getBody()
    {
        if (is_string($this->response['content']) ||
            is_integer($this->response['content']) ||
            is_float($this->response['content']) ||
            is_bool($this->response['content']) ||
            is_null($this->response['content'])) {

            return $this->response['content'];
        }
        return '';
    }

    public function send()
    {
        http_response_code($this->response['code']);
        foreach ($this->response['headers'] as $header) {
            header($header);
        }

        echo $this->getBody();

        return $this;
    }


    public function __toString()
    {
        // TODO: Implement __toString() method.
        http_response_code($this->response['code']);

        foreach ($this->response['headers'] as $header) {
            header($header);
        }

        return $this->getBody();
    }
}
