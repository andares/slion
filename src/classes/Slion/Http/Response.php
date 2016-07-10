<?php
namespace Slion\Http;

use Slion\Meta;
use Slim\Http\Response as RawResponse;

/**
 * Http响应
 *
 * @author andares
 */
abstract class Response extends Meta implements DependenciesTaker {
    protected $_http_code    = 200;

    protected $_http_headers = [
    ];

    public function setHeaders(array $headers = [], $reset = false) {
        $reset && $this->_http_headers = [];

        foreach ($headers as $name => $value) {
            $this->_http_headers[$name] = $value;
        }
    }

    public function applyHeaders(RawResponse $response) {
        // http 头
        if ($this->_http_headers) {
            foreach ($this->_http_headers as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }
    }

    /**
     *
     * @param RawResponse $response
     * @return RawResponse
     */
    public function regress(RawResponse $response) {
        return $response->withJson($this, $this->_http_code);
    }

    public function setHttpCode($code = 200) {
        $this->_http_code = $code;
    }

    public function jsonSerialize() {
        return $this->makeProtocol();
    }

    protected function makeProtocol() {
        return [
            'result'    => $this->toArray(),
            'error'     => null,
        ];
    }

    public function takeDependencies(\Slim\Container $container) {}
}
