<?php
namespace Slion\Http;

use Slim\Http\Request as SlimRequest;
use Slim\Http\Response as SlimResponse;

/**
 * Description of Router
 *
 * @author andares
 */
class Router {
    protected $module;

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     *
     * @var Response
     */
    protected $response;

    public static function direct($space, SlimRequest $request, SlimResponse $response, array $args) {
        $router     = new self($space, $request, $response);

        if (\PHP_VERSION_ID >= 70000) {
            $controller = $args['controller'] ?? 'index';
            $action     = $args['action'] ?? 'main';
        } else {
            $controller = $args['controller'] ? $args['controller'] : 'index';
            $action     = $args['action'] ? $args['action'] : 'main';
        }

        $ext        = explode('/', $request->getAttribute('ext'));
        return $router($controller, $action, $ext);
    }

    public function __construct($module, SlimRequest $request, SlimResponse $response) {
        $this->module   = $module;
        $this->request  = $request;
        $this->response = $response;
    }

    public function __invoke($controller_name, $action, array $ext = []) {
        global $app;
        /* @var $app \Slim\App */
        $class      = $this->getControllerClass($controller_name);
        $controller = new $class($this->request, $this->response, $app->getContainer());
        return $controller->$action(...$ext);
    }

    protected function getControllerClass($name) {
        return "$this->module\\Controllers\\" . ucfirst($name);
    }

}
