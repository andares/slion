<?php

namespace Slion\Http;

use Slim\App;
use Slim\Http\Request as RawRequest;
use Slim\Http\Response as RawResponse;
use Slion\Abort;
use Slion\Debugger;


/**
 * Description of Dispatcher
 *
 * @author andares
 */
class Dispatcher {
    /**
     *
     * @var App
     */
    protected $app;

    /**
     *
     * @param App $app
     */
    public function __construct(App $app) {
        $this->app = $app;
    }

    /**
     *
     * @param string $module
     * @param string $pattern
     * @param array $methods
     * @return self
     */
    public function json(string $module, string $pattern,
        array $methods = ['GET', 'POST', 'PUT', 'PATCH',
            'DELETE', 'OPTIONS']): self {

        $dispatcher = $this;
        $this->app->map($methods, "/$module/{controller}[/{action}[/{more:.*}]]",
            function (RawRequest $request, RawResponse $response, array $args)
            use ($pattern, $dispatcher) {
                $response = $dispatcher($pattern, $args['controller'],
                    $args['action'] ?? 'index',
                    $request, $response);
                /* @var $response Response */

                $this->get('hook')->take(\Slion\HOOK_REGRESS_RESPONSE, $response);
                return $response->regress()->withJson($response);
            })->setName($module);
        return $this;
    }

    /**
     *
     * @todo 有待加入错误页面处理
     *
     * @param string $module
     * @param string $pattern
     * @param array $methods
     * @return self
     */
    public function html(string $module, string $pattern,
        array $methods = ['GET', 'POST', 'PUT', 'PATCH',
            'DELETE', 'OPTIONS']): self {

        $dispatcher = $this;
        $this->app->map($methods, "/$module/{controller}[/{action}[/{more:.*}]]",
            function (RawRequest $request, RawResponse $response, array $args)
            use ($pattern, $dispatcher) {
                $response = $dispatcher($pattern, $args['controller'],
                    $args['action'] ?? 'index',
                    $request, $response);
                /* @var $response Response */

                $this->get('hook')->take(\Slion\HOOK_REGRESS_RESPONSE, $response);
                return $this->renderer->render($response->regress(),
                    $response->getTemplate(), $response->toArray());
            })->setName($module);
        return $this;
    }

    /**
     *
     * @param string $pattern
     * @param string $controller_name
     * @param string $action
     * @param RawRequest $raw_request
     * @param RawResponse $raw_response
     * @return \Slion\Http\Response
     */
    public function __invoke(string $pattern, string $controller_name, string $action,
        RawRequest $raw_request, RawResponse $raw_response): Response {

        try {
            $controller = $this->makeController($pattern, $controller_name,
                $raw_request, $raw_response);
            $response   = $controller->$action();

        } catch (Abort $abort) {
            $response = $this->raiseError($raw_response, $raw_request, $abort());

            // 触发hook
            $this->app->getContainer()->get('hook')
                ->take(\Slion\HOOK_ABORT_CATCHED, $abort, $response);

        } catch (\BadMethodCallException $e) {
            // 处理未定义的接口
            $response = $this->raiseError($raw_response, $raw_request,
                new \BadMethodCallException(
                    "action [$action@$controller_name] is not exists", 0, $e));
            $response->setHttpCode(404);

        } catch (\Throwable $e) {
            $response = $this->raiseError($raw_response, $raw_request, $e);
        }

        // 错误记录与触发debugger handle
        if ($response instanceof ErrorResponse) {
            lg($response->confirm()->toLog());
            $this->app->getContainer()->get('hook')
                ->take(\Slion\HOOK_TAKE_ERRORRESPONSE, $response);
        }
        return $response;
    }

    /**
     *
     * @param string $pattern
     * @param string $name
     * @return \Slion\Http\Controller
     * @throws \BadMethodCallException
     */
    protected function makeController(string $pattern, string $name,
        RawRequest $raw_request, RawResponse $raw_response): Controller {
        $class = $this->getControllerClass($pattern, $name);
        if (!class_exists($class)) {
            throw new \BadMethodCallException(
                "controller [$name] is not exists($class)");
        }
        $controller = new $class($this->app->getContainer(),
            $raw_request, $raw_response);
        return $controller;
    }

    /**
     *
     * @param string $pattern
     * @param string $name
     * @return string
     */
    protected function getControllerClass(string $pattern, string $name): string {
        return str_replace('%c%', ucfirst($name), $pattern);
    }

    /**
     * @todo 未对原response（如果有的话）中的channel项做继承
     *
     * @param RawResponse $raw
     * @param \Throwable $e
     * @return \Slion\Http\ErrorResponse
     */
    protected function raiseError(RawResponse $raw, RawRequest $request, \Throwable $e): ErrorResponse {
        $response = new ErrorResponse($raw, $request, $e);
        return $response;
    }

}
