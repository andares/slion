<?php
namespace Slion\Http;

use Slion\Meta;
use Slion\Components\DependenciesTaker;
use Slim\Http\Response as RawResponse;
use Slim\Collection;

/**
 * Http响应
 *
 * @author andares
 */
abstract class Response extends Meta implements DependenciesTaker {
    protected $_http_code    = 200;

    protected $_http_headers = [
    ];

    /**
     *
     * @var array
     */
    protected $channels = [];

    /**
     *
     * @param string $name
     * @param Collection $data
     */
    public function setChannelData(string $name, Collection $data) {
        $this->channels[$name] = $data;
    }

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

    public function raiseError(\Exception $exc, \Slim\Container $container): self {
        return ErrorResponse::handleException($exc, $container);
    }

    /**
     *
     * @param RawResponse $response
     * @return RawResponse
     */
    public function regress(RawResponse $response) {
        return $response->withJson($this, $this->_http_code);
    }

    public function setHttpCode(int $code = 200) {
        $this->_http_code = $code;
    }

    public function getHttpCode(): int {
        return $this->_http_code;
    }

    public function jsonSerialize() {
        return $this->makeProtocol();
    }

    protected function makeProtocol() {
        return [
            'result'    => $this->toArray(),
            'error'     => null,
            'channels'  => $this->makeChannelArray(),
        ];
    }

    protected function makeChannelArray() {
        $result = [];
        foreach ($this->channels as $name => $data) {
            /* @var $data Collection */
            foreach ($data as $key => $row) {
                if (is_object($row)) {
                    if (method_exists($row, 'toArray')) {
                        $result[$name][$key] = $row->toArray();
                    } else {
                        $result[$name][$key] = json_encode($row);
                    }
                } elseif (is_callable($row)) {
                    $result[$name][$key] = $row();
                } else {
                    $result[$name][$key] = $row;
                }
            }
        }
        return $result;
    }

    public function takeDependencies(\Slim\Container $container) {}
}
