<?php
/**
 * Created by PhpStorm.
 * User: Zenith
 * Date: 2014.06.10.
 * Time: 11:27
 */

namespace Ezapi\Resource;


class Location {

    public $headers = array();
    public $path = '';
    public $body = '';
    public $method = 'GET';
    public $parser = null;

    public function __construct() {
        $args = func_get_args();
        $cargs = count($args);
        if ($cargs === 1) {
            $params = $args[0];
            $this->headers = isset($params['headers']) ? $params['headers'] : array();
            $this->body = isset($params['body']) ? $params['body'] : '';
            $this->method = isset($params['method']) ? $params['method'] : '';
            $this->path = isset($params['path']) ? $params['path'] : '';
            $this->parser = isset($params['parser']) ? $params['parser'] : '';
        } else if ($cargs > 1) {
            $this->method = $args[0] ?: 'GET';
            $this->path = $args[1] ?: '';
            $this->body = $args[2] ?: '';
            $this->headers = $args[3] ?: array();
            $this->parser = $args[4] ?: null;
        }
    }
} 