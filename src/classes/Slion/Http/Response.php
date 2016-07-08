<?php
namespace Slion\Http;

use Slion\Utils\Meta;
use Slim\Http\Response as SlimResponse;

/**
 * Http响应
 *
 * @author andares
 */
abstract class Response extends Meta {
    protected $response;

    protected $message      = '';
    protected $http_code    = 200;
    protected $http_header  = [
        'Content-type'  => 'text/json;charset=utf-8',
    ];

    public function __construct(array $data = null, SlimResponse $response) {
        parent::__construct($data);
        $this->response = $response;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function setHttpCode($code = 200) {
        $this->http_code = $code;
    }

    public function setHeader(array $header = [], $reset = false) {
        $reset && $this->http_header = [];

        foreach ($header as $name => $value) {
            $this->http_header[$name] = $value;
        }
    }

    public function applyHeader() {
        foreach ($this->http_header as $name => $value) {
            header("$name:$value");
        }
    }

    public function jsonSerialize() {
        return [
            'data'  => $this->toArray(),
            'error' => 0,
            'msg'   => $this->message,
        ];
    }
}
