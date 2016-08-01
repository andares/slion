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

    public function __construct(Dispatcher $dispatcher = null) {
        $this->dispatcher = $dispatcher;
    }

    public function __call($name, array $arguments) {
        $this->hook && $this->hook->take(\Slion\HOOK_BEFORE_ACTION, $this, $name, ...$arguments);

        $action = ucfirst($name);
        $method = "action$action";

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("action [$action] is not exist");
        }

        $this->$method(...$arguments);
    }

    public function call($controller_name, $action, array $ext = []) {
        if (!$this->dispatcher) {
            return null;
        }
        return $this->dispatcher->call($controller_name, $action, $ext);
    }

    public function getUploadedFiles() {
        if ($this->dispatcher) {
            return $this->dispatcher->getRawRequest()->getUploadedFiles();
        }
        return [];
    }

    public function __get($name) {
        return $this->dispatcher ? $this->dispatcher->get($name) : null;
    }
}
