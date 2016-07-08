<?php
namespace Slion\Http;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Description of Router
 *
 * @author andares
 */
class Router {
    protected $module;
    protected $request;
    protected $response;

    public static function direct($space, Request $request, Response $response, array $args) {
        $router     = new self($space, $request, $request);
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

    public function __construct($module, Request $request, Response $response) {
        $this->module   = $module;
        $this->request  = $request;
        $this->response = $response;
    }

    public function __invoke($controller_name, $action, array $ext = []) {
        $class      = $this->getControllerClass($controller_name);
        $controller = new $class;
        return $controller->$action(...$ext);
    }

    protected function getControllerClass($name) {
        return "$this->module\\Controllers\\" . ucfirst($name);
    }

}
