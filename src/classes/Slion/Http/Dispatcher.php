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

    public function __construct($space, Container $container,
        RawRequest $request, RawResponse $response) {
        $this->container    = $container;
        $this->space        = $space;
        $this->request      = $request;
        $this->response     = $response;

        // 注册自己
        $container['dispatcher'] = function($c) {
            return $this;
        };
    }

    public function get($name) {
        return $this->container->get($name);
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
        $response = $this->call($controller_name, $action, $ext);

        $this->get('hook')->take(\Slion\HOOK_REGRESS_RESPONSE, $this, $response);
        return $response->regress($this->response);
    }

    protected function getControllerClass($name) {
        return "$this->space\\Controllers\\" . ucfirst($name);
    }

    public function call($controller_name, $action, array $ext = []) {
        try {
            // 生成controller
            $class = $this->getControllerClass($controller_name);
            if (!class_exists($class)) {
                throw new \BadMethodCallException("controller [$controller_name] is not exists");
            }
            $controller = new $class($this);

            // 生成access对象
            $access_object = $this->makeAccessMessage($controller, $action);
            $response = $access_object[0];
            /* @var $response Response */

            if (isset($access_object[1]) && $access_object[1] instanceof Request) {
                $request = $access_object[1];
            } else {
                $request = '[]';
            }
            assert($this->_log4request($request));

            $controller->$action(...$access_object, ...$ext);
            $response->confirm();
            $response->applyHeaders($this->response);

            $this->get('hook')->take(\Slion\HOOK_BEFORE_RESPONSE, $this, $response);
        } catch (\BadMethodCallException $exc) {
            // 处理未定义的接口
            return $this->raiseError(
                new \BadMethodCallException("action [$action@$controller_name] is not exists"),
                404);

        } catch (\Throwable $exc) {
            if (isset($response)) {
                /* @var $response Response */
                $response = $response->raiseError($exc, $this->container);
            } else {
                $response = $this->raiseError($exc);
            }
        }

        assert($this->_log4response($response));
        return $response;
    }

    protected function raiseError(\Throwable $exc, int $code = 0) {
        $response = ErrorResponse::handleException($exc, $this->container);
        $code && $response->setHttpCode($code);
        return $response;
    }

    protected function makeAccessMessage(Controller $controller, $action) {
        $prefix = get_class($controller) . '\\' . ucfirst($action);

        // 创建response
        $response_class = "{$prefix}Response";
        if (!class_exists($response_class)) {
            throw new \BadMethodCallException("response of action [$action] is not exist");
        }
        $response   = new $response_class();
        /* @var $response Response */
        $response->takeDependencies($this->container);

        // 创建request
        $request_class  = "{$prefix}Request";
        if (@class_exists($request_class)) {
            $request = new $request_class($this->request->getParams());
            /* @var $request Request */
            $request->takeDependencies($this->container);
            $request->confirm();
        } else {
            $request = null;
        }

        return [$response, $request];
    }

    protected function _log4request($request) {
        $logger = $this->get('logger');
        if ($logger) {
            $logger->info("receive request:$request");
        }
        return true;
    }

    protected function _log4response($response) {
        $logger = $this->get('logger');
        if ($logger) {
            $logger->info("send response(" . $response->getHttpCode() . "):$response");
        }
        return true;
    }

}
