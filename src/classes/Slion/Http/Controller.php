<?php
namespace Slion\Http;


/**
 * 默认控制器
 *
 * @author andares
 *
 * @property \Slion\Utils\Config $config
 * @property \Slion\Utils\Dict $dict
 * @property \Slion\Utils\Logger $logger
 */
abstract class Controller {
    /**
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     *
     * @var Container
     */
    protected $container = null;

    public function __construct(Dispatcher $dispatcher = null) {
        $this->dispatcher = $dispatcher;
    }

    public function __call($name, array $arguments) {
        $action = ucfirst($name);
        $method = "action$action";

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("method not exist $action@" . __DIR__);
        }

        if (isset($arguments[1]) && $arguments[1] instanceof Request) {
            $request = $arguments[1];
        } else {
            $request = '[]';
        }
        $this->_log4request($request);
        $this->$method(...$arguments);
        $this->_log4response($arguments[0]);
    }

    public function call($controller_name, $action, array $ext = []) {
        if (!$this->dispatcher) {
            return null;
        }
        return $this->dispatcher->call($controller_name, $action, $ext);
    }

    public function __get($name) {
        return $this->dispatcher ? $this->dispatcher->get($name) : null;
    }

    protected function _log4request($request) {
        if ($this->logger) {
            $this->logger->info("receive request:$request", ['controller']);
        }
        return true;
    }

    protected function _log4response($response) {
        $this->logger && $this->logger->info("send response:$response", ['controller']);
        return true;
    }
}
