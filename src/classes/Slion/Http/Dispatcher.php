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
     * @param string $space
     * @param array $methods
     * @return self
     */
    public function json(string $module, string $space,
        array $methods = ['GET', 'POST', 'PUT', 'PATCH',
            'DELETE', 'OPTIONS']): self {

        $dispatcher = $this;
        $this->app->map($methods, "/$module/{controller}[/{action}[/{more:.*}]]",
            function (RawRequest $request, RawResponse $response, array $args)
            use ($space, $dispatcher) {
                $response = $dispatcher($space, $args['controller'],
                    $args['action'] ?? '',
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
     * @param string $space
     * @param array $methods
     * @return self
     */
    public function html(string $module, string $space,
        array $methods = ['GET', 'POST', 'PUT', 'PATCH',
            'DELETE', 'OPTIONS']): self {

        $dispatcher = $this;
        $this->app->map($methods, "/$module/{controller}[/{action}[/{more:.*}]]",
            function (RawRequest $request, RawResponse $response, array $args)
            use ($space, $dispatcher) {
                $response = $dispatcher($space, $args['controller'],
                    $args['action'] ?? '',
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
     * @param string $space
     * @param string $controller_name
     * @param string $action
     * @param RawRequest $raw_request
     * @param RawResponse $raw_response
     * @return \Slion\Http\Response
     */
    public function __invoke(string $space, string $controller_name, string $action,
        RawRequest $raw_request, RawResponse $raw_response): Response {

        try {
            $controller = $this->makeController($space, $controller_name,
                $raw_request, $raw_response);
            $response   = $controller->$action();

        } catch (Abort $abort) {
            $response = $this->raiseError($raw_response, $abort());

            // 触发hook
            $this->app->getContainer()->get('hook')
                ->take(\Slion\HOOK_ABORT_CATCHED, $abort, $response);

        } catch (\BadMethodCallException $e) {
            // 处理未定义的接口
            $response = $this->raiseError($raw_response,
                new \BadMethodCallException(
                    "action [$action@$controller_name] is not exists"));
            $response->setHttpCode(404);

        } catch (\Throwable $e) {
            $response = $this->raiseError($raw_response, $e);
        }

        // 错误记录与触发debugger handle
        if ($response instanceof ErrorResponse) {
            dlog($response->confirm()->toLog());
        }
        return $response;
    }

    /**
     *
     * @param string $space
     * @param string $name
     * @return \Slion\Http\Controller
     * @throws \BadMethodCallException
     */
    protected function makeController(string $space, string $name,
        RawRequest $raw_request, RawResponse $raw_response): Controller {
        $class = $this->getControllerClass($space, $name);
        if (!class_exists($class)) {
            throw new \BadMethodCallException("controller [$name] is not exists");
        }
        $controller = new $class($this->app->getContainer(),
            $raw_request, $raw_response);
        return $controller;
    }

    /**
     *
     * @param string $space
     * @param string $name
     * @return string
     */
    protected function getControllerClass(string $space, string $name): string {
        return "$space\\Controllers\\" . ucfirst($name);
    }

    /**
     * @todo 未对原response（如果有的话）中的channel项做继承
     *
     * @param RawResponse $raw
     * @param \Throwable $e
     * @return \Slion\Http\ErrorResponse
     */
    protected function raiseError(RawResponse $raw, \Throwable $e): ErrorResponse {
        $response = new ErrorResponse($raw, $e);
        return $response;
    }

}
