<?php

namespace Ezapi\Resource;

class Generic {

    /**
     * @var array
     */
    protected $methodMap = array();
    /**
     * @var array
     */
    protected $chain = array();
    /**
     * @var array
     */
    protected $chainParams = array();

    /**
     * @var callable
     */
    protected $locationFactory;

    /**
     * @param null|array|Methodmap\Collection $methodMap
     */
    public function __construct($methodMap = null)
    {
        if ($methodMap) {
            $this->setMethodMap($methodMap);
        }
    }

    /**
     * @param callable $factory
     * @return $this
     */
    public function setLocationFactory(Callable $factory)
    {
        $this->locationFactory = $factory;
        return $this;
    }

    /**
     * @return callable
     */
    public function getLocationFactory()
    {
        if (!$this->locationFactory) {
            $this->setLocationFactory(self::getDefaultLocationFactory());
        }
        return $this->locationFactory;
    }

    /**
     * @return callable
     */
    public static function getDefaultLocationFactory()
    {
        return function ($params) {
            return new Location($params);
        };
    }

    /**
     * Set method map collection
     * @param array|Methodmap\Collection $methodMap
     * @return $this
     */
    public function setMethodMap($methodMap)
    {
        if ($methodMap instanceof Methodmap\Collection) {
            $methodMap = $methodMap->getMap();
        } else if (!is_array($methodMap)) {
            throw new \InvalidArgumentException(
                'Methodmap is not accepted',
                E_USER_ERROR);
        }
        $this->methodMap = $methodMap;
        return $this;
    }

    /**
     * Get method map
     * @return array
     */
    public function getMethodMap() {
        return $this->methodMap;
    }


    /**
     * Build location based on name, methodmap params and arguments
     * @param string $name method name
     * @param array $params methodmap
     * @param array $args arguments passed to the location
     * @return Location
     */
    public function buildLocation($name, $params, array $args = array()) {

        if (is_callable($params)) {
            return call_user_func_array($params, $args);
        }

        $path = $params;
        $headers = array();
        $method = $name;
        $body = null;
        if (is_array($params)) {
            $path = $params['path'];
            $method = isset($params['method']) ? $params['method'] : $method;
            $headers = isset($params['headers']) ? $params['headers'] : $headers;
        }
        $args = $args ?: array();

        $path = preg_replace_callback(
            '@/(?P<p>:!?param(?P<i>\d))@',
            function ($matches) use (&$args) {
                $index = $matches['i'];
                $param = $matches['p'];
                $argsIndex = $index - 1;
                $required = $param{1} === '!';
                if (isset($args[$argsIndex]) && !is_array($args[$argsIndex])) {
                    $return = $args[$argsIndex];
                    unset($args[$argsIndex]);
                    return '/' . $return;
                } else if ($required) {
                    throw new \InvalidArgumentException('Required parameter missing: '.$param);
                } else {
                    return '';
                }
            },
            $path);

        if (!empty($args)) {
            $remaining = array_values($args);
            $body = $remaining[0];
            if (isset($remaining[1]) && is_array($remaining[1])) {
                $headers = array_merge($headers, $remaining[1]);
            }
        }

        if (isset($args['path'])) {
            $path = $args['path'];
        }
        if (isset($args['body'])) {
            $body = $args['body'];
        }

        return $this->createLocation(
            $name,
            array(
                'path' => $path,
                'method' => $method,
                'headers' => $headers,
                'body' => $body));

    }

    /**
     * Add/Connect the given resource under the specified name
     * Get the resource under the name
     * @param string $name
     * @param Generic $resource
     * @return $this|Generic
     */
    public function chain($name, Generic $resource = null)
    {
        if (!$resource) {
            return isset($this->chain[$name])
                ? $this->chain[$name]
                : null;
        }

        if ($name !== '__reverse') {
            $resource->chain('__reverse', $this);
        }

        $this->chain[$name] = $resource;

        return $this;
    }

    /**
     * Get chain params
     * @return array
     */
    public function getChainParams() {
        return $this->chainParams;
    }

    /**
     * Return true if this resource is chained to another
     * @return bool
     */
    public function isChained() {
        return isset($this->chain['__reverse']);
    }

    /**
     * Set chain params
     * @param array $arguments
     * @return $this
     */
    public function setChainParams(array $arguments = null) {
        $this->chainParams = $arguments;
        return $this;
    }

    /**
     * Set up chain
     * @return $this
     */
    public function __invoke() {
        return $this->setChainParams(func_get_args());
    }

    /**
     * Return chained resource under the given name
     * @param $name
     * @throws \ErrorException
     */
    public function __get($name) {
        if (($chain = $this->chain($name))) {
            return $chain;
        }
        throw new \ErrorException('Undefined property: '.$name, E_USER_NOTICE);
    }

    /**
     * Call buildLocation when method name is found in methodmap
     * @param $name
     * @param array $args
     */
    public function __call($name, array $args = null) {
        if (isset($this->methodMap[$name])) {
            return $this->buildLocation($name, $this->methodMap[$name], $args);
        }

        throw new \BadMethodCallException('Method not found: '.$name, E_USER_ERROR);
    }

    /**
     * @param string $method
     * @param array $params
     * @return Location
     */
    protected function createLocation($method, array $params)
    {
        if ($this->isChained()) {
            $reverse = $this->chain('__reverse');
            if (!is_callable(array($reverse, $method))) {
                throw new \InvalidArgumentException(
                    'Requested chain method not available',
                    E_USER_ERROR);
            }
            $reverseParams = $reverse->getChainParams();
            $Location = call_user_func_array(
                array($reverse, $method),
                $reverseParams);

            $reverse->setChainParams();
            $params['path'] = $Location->path . $params['path'];
            return $this->factoryLocation($params);
        }

        return $this->factoryLocation($params);
    }

    /**
     * Use getLocationFactory to create Location
     * @param array $params
     * @return Location
     */
    public function factoryLocation($params)
    {
        $callback = $this->getLocationFactory();
        return $callback($params);
    }
} 