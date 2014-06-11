<?php

namespace Ezapi;

use Ezapi\Resource\Generic;
use Ezapi\Resource\Location;

class Api {

    protected static $resources = array();

    protected $uri = '';

    protected $communicator;

    protected static $defaultCommunicator = 'Ezapi\Communicator\Curl';

    /**
     * @return CommunicatorInterface
     * @throws \ErrorException
     */
    public function getCommunicator() {
        if (!$this->communicator) {
            $this->setCommunicator(self::getDefaultCommunicator());
        }
        return $this->communicator;
    }

    /**
     * @param CommunicatorInterface $communicator
     * @return $this
     */
    public function setCommunicator(CommunicatorInterface $communicator) {
        $this->communicator = $communicator;
        return $this;
    }

    /**
     * @return CommunicatorInterface
     * @throws \ErrorException
     */
    public static function getDefaultCommunicator() {
        $object = new self::$defaultCommunicator;
        if (false === $object instanceof CommunicatorInterface) {
            throw new \ErrorException(
                'Default communicator does not implement CommunicatorInterface',
                E_USER_ERROR);
        }
        return $object;
    }

    /**
     * @param string $className fully qualified classname
     */
    public static function setDefaultCommunicator($className) {
        self::$defaultCommunicator = $className;
    }

    /**
     * @param Location $location
     * @return Response
     */
    public function query(Location $location) {
        $location->uri = $this->uri() . $location->uri;
        return $this->getCommunicator()->query($location);
    }

    /**
     * @param Location $location
     * @return Response
     */
    public function ask(Location $location) {
        return $this->query($location);
    }

    /**
     * @param string $value base uri
     * @return $this
     */
    public function setBaseUri($value) {
        $this->uri = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUri() {
        return $this->uri;
    }

    /**
     * base uri setter/getter
     * @param null $value
     * @return $this|string
     */
    public function uri($value = null) {
        return $value === null
            ? $this->getBaseUri()
            : $this->setBaseUri($value);
    }

    /**
     * @param $value
     * @return Resource
     * @throws \InvalidArgumentException
     */
    public function __get($value) {
        if ($resource = $this->getResource($value)) {
            return $resource;
        }
        throw new \InvalidArgumentException('Not found resource:' .$value);
    }

    public function __set($key, $value) {
        return $this->setResource($key, $value);
    }

    public function __call($name, $arguments) {
        $resource = $this->__get($name);
        if (is_callable($resource)) {
            return call_user_func_array($resource, $arguments);
        }
        throw new BadMethodCallException('Method not found: '.$name);
    }

    /**
     * @param string $name
     * @return Resource
     */
    public function getResource($name) {
        return self::resourceFactory($name);
    }

    /**
     * @param string|Generic $key
     * @param Generic|null $value
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setResource($key, $value = null) {
        if (!$value) {
            $key = get_class($key);
            $value = $key;
        }
        if (false === $value instanceof Generic) {
            throw new \InvalidArgumentException(
                'The given resource does not implement Resource\Generic',
                E_USER_ERROR);
        }
        self::$resources[$key] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Generic
     * @throws \ErrorException
     * @throws \InvalidArgumentException
     */
    public static function resourceFactory($name) {
        $classNames = array(
            $name,
            'Ezapi\\Resource\\'.$name,
            'Ezapi\\Resource\\'.ucfirst($name));

        foreach($classNames as $className) {
            if (!isset(self::$resources[$className])) {
                if (class_exists($className, 1)) {
                    $obj =  new $className;
                    if (false === $obj instanceof Resource) {
                        throw new \ErrorException(
                            'Resource "'.$className.'" does not implement Resource\Generic',
                            E_USER_ERROR);
                    }
                    self::$resources[$className] = $obj;
                    return $obj;
                }
            } else {
                return self::$resources[$name];
            }
        }
        throw new \InvalidArgumentException(
            'Not accepted classname: '.$name,
            E_USER_ERROR);
    }
}