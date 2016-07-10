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

        $this->_log4request($arguments[0]);
        $this->$method(...$arguments);
        $this->_log4response($arguments[1]);
    }

    public function __get($name) {
        return $this->dispatcher->get($name);
    }

    protected function _log4request($request) {
        $this->logger->info("receive request:$request", ['controller']);
    }

    protected function _log4response($response) {
        $this->logger->info("send response:$response", ['controller']);
    }
}
