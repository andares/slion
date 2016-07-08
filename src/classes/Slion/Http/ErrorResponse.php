<?php

/*
 * license less
 */

namespace Slion\Http;

/**
 *
 * @author andares
 */
class ErrorResponse extends Response {
    /**
     *
     * @var \Exception
     */
    private $exc;

    private $error = 0;

    /**
     * @todo 暂时还是先200
     * @var int
     */
    protected $http_code    = 200;

    public function __call($name, $arguments) {
        $this->exc->$name(...$arguments);
    }

    public function setCode($code) {
        $this->error = $code;
    }

    public function by(\Exception $exc) {
        $this->exc = $exc;

        $this->error    = $exc->getCode();
        $message        = \trans('error_response', $this->error);
        $this->message  = $message == $this->error ? $exc->getMessage() : $message;
    }

    public function jsonSerialize() {
        return [
            'data'  => $this->toArray(),
            'error' => $this->error,
            'msg'   => $this->message,
        ];
    }

}
