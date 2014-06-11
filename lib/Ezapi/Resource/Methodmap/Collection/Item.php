<?php

namespace Ezapi\Resource\Methodmap\Collection;


class Item
{

    protected $params = array();
    protected $headers = array();
    protected $name;

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PUT = 'PUT';
    const METHOD_OPTION = 'OPTION';
    const METHOD_HEAD = 'HEAD';

    /**
     * Set/Get the name of the item
     * @param null|string $name
     * @return string|$this
     */
    public function name($name = null)
    {
        if ($name === null) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Set/get the callback used to generate the resource location
     * @param callable|null $callback
     * @return $this|null
     */
    public function callback(Callable $callback = null)
    {
        return $this->param('callback', $callback);
    }

    /**
     * Set/get the item's path
     * @param null|string $path
     * @return $this|string
     */
    public function path($path = null)
    {
        return $this->param('path', $path);
    }

    /**
     * Set/get the item's http method
     * @param null|string $method
     * @return $this|string
     */
    public function method($method = null)
    {
        if ($method
            && ($method = strtoupper($method))
            && !in_array($method, $this->availableMethods()) ) {
            throw new \InvalidArgumentException(
                'Method not accepted',
                E_USER_ERROR);
        }
        return $this->param('method', $method);
    }

    /**
     * Set/get the parser callback used to parse the api result
     * @param callable|null $callback
     * @return $this|callable
     */
    public function parser(Callable $callback = null)
    {
        return $this->param('parser', $callback);
    }

    /**
     * Set/get additional headers sent with the request
     * @param null|string $key
     * @param null|string $value
     * @return $this|array|null
     */
    public function header($key = null, $value = null)
    {
        if (!$key) {
            return $this->headers;
        }

        if (!$value) {
            return isset($this->headers[$key])
                ? $this->headers[$key]
                : null;
        }
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set/get the default body sent with the request
     * @param null|mixed $body
     * @return $this|mixed
     */
    public function body($body = null)
    {
        return $this->param('body', $body);
    }

    /**
     * Set/get body, method, parser, callback, path
     * @param $key
     * @param mixed|$value
     * @return $this|mixed
     */
    public function param($key, $value = null)
    {
        if (!$value) {
            return isset($this->params[$key])
                ? $this->params[$key]
                : null;
        }
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Converts item to array
     * @return array
     */
    public function toArray() {
        if (!$this->name()) {
            throw new \InvalidArgumentException('Name is not set', E_USER_ERROR);
        }
        if (!$this->method()) {
            throw new \InvalidArgumentException('Method is not set', E_USER_ERROR);
        }


        $return = $this->params;
        $return['name'] = $this->name();
        $return['headers'] = $this->headers;
        return $return;
    }

    /**
     * Return available methods
     * @return array
     */
    public function availableMethods() {
        return array(
            self::METHOD_DELETE,
            self::METHOD_GET,
            self::METHOD_PUT,
            self::METHOD_POST,
            self::METHOD_OPTION,
            self::METHOD_HEAD,
        );
    }

} 