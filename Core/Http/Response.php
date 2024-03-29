<?php

namespace Core\Http;

use InvalidArgumentException;

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
     * @param mixed $headers
     */
    public function __construct($content = null, $code = 200, array $headers = null)
    {
        $this->content($content);
        $this->code($code);
        $this->headers($headers);
    }

    /**
     * set content
     * @param $content
     * @return $this
     */
    public function content($content): self
    {
        $this->response['content'] = $content;
        return $this;
    }

    /**
     * http response code
     * @param int $code
     * @return $this
     */
    public function code(int $code): self
    {
        $this->response['code'] = $code;
        return $this;
    }

    /**
     * set header
     * @param string|array $headers
     * @return $this
     */
    public function headers($headers = null): self
    {
        if (is_array($headers)) {
            foreach ($headers as $key => $value) {
                if (is_integer($key)) {
                    $this->response['headers'][] = $value;
                } else {
                    $this->response['headers'][] = $key . ': ' . $value;
                }
            }
        } elseif ($headers) {
            $this->response['headers'][] = $headers;
        }

        return $this;
    }


    /**
     * @param null $options
     * @return $this
     */
    public function toJson($options = null): self
    {
        $this->headers(['Content-Type' => 'application/json']);

        $this->response['content'] = json_encode($this->response['content'], $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON decode edilemedi.', E_NOTICE);
        }

        return $this;
    }


    /**
     * @param null $url
     * @param int $code
     */
    public function redirect($url = null, $code = 302)
    {
        $this->headers(['Location' => $url])->code($code)->send();
        exit;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->response['code'] ?? http_response_code();
    }


    /**
     * @return array|mixed
     */
    public function getHeader()
    {
        return $this->response['headers'] + headers_list();
    }


    /**
     * Header gönderilmemişse temizlenir
     */
    public function clearHeader(): void
    {
        $this->response['headers'] = [];

        if (!headers_sent()) {
            header_remove();
        }
    }

    /**
     * Yanıt gövdesini döndürür
     * @return string
     */
    public function getBody(): string
    {
        if (is_resource($this->response['content'])) {

            return 'Resource Type: ' . get_resource_type($this->response['content']);
        } elseif (is_array($this->response['content']) || is_object($this->response['content'])) {

            $this->toJson();
        }

        return (string)$this->response['content'];
    }

    /**
     * print body to screen
     */
    public function send()
    {
        echo $this->setResponse();
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->setResponse();
    }

    /**
     * @return string
     */
    private function setResponse(): string
    {
        $body = $this->getBody();

        http_response_code($this->response['code']);
        foreach ($this->response['headers'] as $header) {
            header($header);
        }

        return $body;
    }
}
