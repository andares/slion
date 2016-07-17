<?php
namespace Slion\Http;

/**
 *
 * @author andares
 */
class ErrorResponse extends Response {
    /**
     * 错误词典名
     * @var string
     */
    protected static $_message_dict = 'errors';

    /**
     *
     * @var \Throwable
     */
    private $_exc;

    protected $_error_code = 0;

    /**
     * @var int
     */
    protected $_http_code    = 200;

    protected $_message = '';

    /**
     *
     * @param \Throwable $exc
     * @return self
     */
    public static function handleException(\Throwable $exc, \Slim\Container $container) {
        if ((is_prod() || $exc->getCode()) ||
            !$container->get('slion_settings')['debug']['debug_in_web']) {
            $response = new static([]);
            $response->by($exc);

            $container->get('hook')->take(\Slion\HOOK_ERROR_RESPONSE, $response, $exc);
            return $response->confirm();
        }

        return static::debugException($exc);
    }

    protected static function debugException(\Throwable $exc) {
        return \Slion\Debugger::exceptionHandler($exc, true);
    }

    public function __call($name, $arguments) {
        $this->_exc->$name(...$arguments);
    }

    public function setErrorCode($code) {
        $this->_error_code = $code;
    }

    public function setMessage($message) {
        $this->_message = $message;
    }

    /**
     * @param \Throwable $exc
     */
    public function by(\Throwable $exc) {
        $this->_exc = $exc;

        $this->_error_code  = $exc->getCode();
        $message = \tr(static::$_message_dict, $this->_error_code);
        if (!is_prod() && $this->_error_code === 0) {
            $this->_message = $exc->getMessage();
        } else {
            $this->_message = ($message === $this->_error_code) ? $exc->getMessage() : $message;
        }
    }

    protected function makeProtocol() {
        return [
            'result'    => null,
            'error'     => [
                'message'   => $this->_message,
                'code'      => $this->_error_code,
                'data'      => $this->toArray(),
            ],
            'channels'  => [],
        ];
    }

}
