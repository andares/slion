<?php

namespace Slion\Http;

use Slim\Container;
use Slim\Http\Request as RawRequest;
use Slim\Http\Response as RawResponse;


/**
 * Description of Dispatcher
 *
 * @author andares
 */
class Dispatcher {
    /**
     *
     * @var Container
     */
    protected $container;

    protected $space;

    /**
     *
     * @var RawRequest
     */
    protected $request;

    /**
     *
     * @var RawResponse
     */
    protected $response;

    public function __construct(Container $container, $space,
        RawRequest $request, RawResponse $response) {
        $this->container    = $container;
        $this->space        = $space;
        $this->request      = $request;
        $this->response     = $response;
    }

    public function get($name) {
        return $this->container->$name;
    }

    /**
     *
     * @return RawRequest
     */
    public function getRawRequest() {
        return $this->request;
    }

    /**
     *
     * @return RawResponse
     */
    public function getRawResponse() {
        return $this->response;
    }

    public function route($controller_name, $action, array $ext = []) {
        $class      = $this->getControllerClass($controller_name);
        $controller = new $class($this);
        return $this->callAction($controller, $action, $ext);
    }

    protected function getControllerClass($name) {
        return "$this->space\\Controllers\\" . ucfirst($name);
    }

    protected function callAction(Controller $controller, $action, array $ext) {
        try {
            list($request, $response) = $this->makeAccessMessage($controller, $action);
            /* @var $request Request */
            /* @var $response Response */

            $controller->$action($request, $response, ...$ext);
            $response->confirm();
            $response->applyHeaders($this->response);
        } catch (\Exception $exc) {
            $response = $this->handleException($exc, $this->response);
        }

        return $response->regress($this->response);
    }

    protected function handleException(\Exception $exc, RawResponse $response) {
        return ErrorResponse::handleException($exc, $response);
    }

    protected function makeAccessMessage(Controller $controller, $action) {
        $prefix = get_class($controller) . '\\' . ucfirst($action);

        // 创建request
        $request_class  = "{$prefix}Request";
        $request = new $request_class($this->request->getParams());
        /* @var $request Request */
        $request->takeDependencies($this->container);
        $request->confirm();

        // 创建response
        $response_class = "{$prefix}Response";
        $response   = new $response_class();
        /* @var $response Response */
        $response->takeDependencies($this->container);

        return [$request, $response];
    }

}
