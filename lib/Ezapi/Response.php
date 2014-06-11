<?php

namespace Ezapi;

class Response {

    /**
     * @var array
     */
    protected $params = array(
        'headers' => array(),
        'rawBody' => null,
        'body' => null
    );

    /**
     * @param null $body
     * @param array $headers
     */
    public function __construct($body = null, array $headers = array()) {
        $this->headers = $headers;
        $this->rawBody = $body;
    }

    /**
     * @param string $name
     * @return null|mixed
     */
    public function __get($name) {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        throw new \InvalidArgumentException('Not found parameter: '.$name, E_USER_ERROR);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        $this->params[$name] = $value;
        if ($name === 'rawBody') {
            $this->__set('body', $this->parseBody($value));
        }
    }

    /**
     * Parse body based on content type
     * @param string $value
     * @return mixed
     */
    protected function parseBody($value) {
        $body = $value;
        if (strpos($this->header('content-type'), 'json') !== false
            && is_string($body)) {
            $body = json_decode($body, 1);
        }
        return $body;
    }

    /**
     * Get http status code
     * @return int
     */
    public function status() {
        return (int)$this->header('status');
    }

    /**
     * Get parsed http body
     * @return mixed|null
     */
    public function body() {
        return $this->body;
    }

    /**
     * Return true when
     * @return bool
     */
    public function isOk() {
        return $this->status() === 200;
    }

    public function header($name) {
        $name = strtolower($name);
        return isset($this->params['headers'][$name])
            ? $this->params['headers'][$name]
            : null;
    }
} 