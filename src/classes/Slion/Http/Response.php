<?php
namespace Slion\Http;

use Slion\Utils\Meta;
use Slim\Http\Response as SlimResponse;

/**
 * Http响应
 *
 * @author andares
 */
abstract class Response extends Meta implements DependenciesTaker {
    /**
     *
     * @var SlimResponse
     */
    protected $_response;

    protected $_http_code    = 200;

    public function __construct(array $data, SlimResponse $response) {
        parent::__construct($data);
        $this->_response    = $response;
    }

    public function __call($name, $arguments) {
        return $this->request->$name(...$arguments);
    }

    public function regress() {
        return $this->_response->withJson($this, $this->_http_code);
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
