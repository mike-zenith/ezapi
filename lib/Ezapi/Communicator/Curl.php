<?php


namespace Ezapi\Communicator;

use Ezapi\CommunicatorInterface;
use Ezapi\Resource\Location;
use Ezapi\Response;

class Curl implements CommunicatorInterface
{

    protected $curl;

    protected $responseBody = '';
    protected $responseHeaders = array();
    protected $responseParser = null;


    public function clean()
    {
        $this->responseHeaders = array();
        $this->responseBody = '';
        $this->responseParser = null;
    }

    public function query(Location $resource)
    {
        $this->clean();

        $method = $resource->method;
        $uri = $resource->uri;
        $headers = $resource->headers;
        $body = $resource->body;
        $this->responseParser = $resource->parser;

        $curlMethod = strtoupper($method);

        if ($curlMethod === 'POST' || $curlMethod === 'PUT') {
            $headers['content-type'] = 'application/json';
            $headers['accept'] = 'application/json';
        }

        $curlMethod = $curlMethod === 'GET' ? 'HTTPGET' : $curlMethod;

        $finalHeaders = array();
        foreach($headers as $header => $content) {
            if (is_string($header)) {
                $finalHeaders[] = $header.': '.$content;
            } else {
                $finalHeaders[] = $content;
            }
        }

        // var_dump($finalHeaders);
        $this->curl = curl_init($uri);
        curl_setopt($this->curl, CURLOPT_URL, $uri);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $finalHeaders);
        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'parseHeader'));

        switch($curlMethod) {
            case 'DELETE':
            case 'HEAD':
            case 'OPTION':
            case 'PUT':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $curlMethod);
                break;
            default:
                curl_setopt($this->curl, constant('CURLOPT_' . $curlMethod), 1);
                break;
        }

        if (is_array($body)) {
            $body = json_encode($body);
        }
        // var_dump('body', $body);
        if ($body) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        }
        // var_dump('send', $uri, $headers, $body);
        $result = curl_exec($this->curl);
        $this->responseBody = $result;

        curl_close($this->curl);
        return $this->parseResult();
    }

    protected function parseResult()
    {
        if ($this->responseParser) {
            if (is_callable($this->responseParser, false)) {
                return call_user_func_array($this->responseParser, array($this->responseBody, $this->responseHeaders));
            } else {
                throw new \InvalidArgumentException(
                    'Response parser not callable:' . var_export($this->responseParser, 1),
                    E_USER_ERROR);
            }
        }

        return $this->createResponse($this->responseBody, $this->responseHeaders);
    }

    protected function createResponse($body = '', array $headers = array()) {
        return new Response($body, $headers);
    }

    public function parseHeader($curl, $headerLine)
    {
        if (strpos($headerLine, 'HTTP/') !== false) {
            $key = 'status';
            $tmp = explode(' ', $headerLine);
            $content = $tmp[1];
        } else if (($headPos = strpos($headerLine, ':')) !== false) {
            $key = substr($headerLine, 0, $headPos);
            $content = trim(substr($headerLine, $headPos + 1));
        } else {
            return strlen($headerLine);
        }
        $key = strtolower($key);
        $this->responseHeaders[$key] = $content;
        return strlen($headerLine);
    }


}