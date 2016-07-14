<?php
namespace Slion\Http;
use Tracy\Debugger;

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
     * @var \Exception
     */
    private $_exc;

    protected $_error_code = 0;

    /**
     * @todo 暂时还是先200
     * @var int
     */
    protected $_http_code    = 200;

    protected $_message = '';

    /**
     *
     * @param \Exception $exc
     * @return self
     */
    public static function handleException(\Exception $exc, \Slim\Container $container) {
        if (is_prod() || $exc->getCode()) {
            $response = new static([]);
            $response->by($exc);

            $container->get('hook')->take(\Slion\HOOK_ERROR_RESPONSE, $response, $exc);
            return $response->confirm();
        }

        \Tracy\Debugger::exceptionHandler($exc, true);
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

    public function by(\Exception $exc) {
        $this->_exc = $exc;

        $this->_error_code  = $exc->getCode();
        $message = \tr(static::$_message_dict, $this->_error_code);
        $this->_message     = $message == $this->_error_code ? $exc->getMessage() : $message;
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
