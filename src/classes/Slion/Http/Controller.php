<?php
namespace Slion\Http;

use Slim\Http\Request as SlimRequest;
use Slim\Http\Response as SlimResponse;
use Slim\Container;


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
     * @var SlimRequest
     */
    protected $request;

    /**
     *
     * @var SlimResponse
     */
    protected $response;

    /**
     *
     * @var Container
     */
    protected $container;

    public function __construct(SlimRequest $request, SlimResponse $response, Container $container) {
        $this->request      = $request;
        $this->response     = $response;
        $this->container    = $container;
    }

    public function __call($name, $arguments) {
        $action = ucfirst($name);
        $method = "action$action";

        try {
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException("method not exist $action@" . __CLASS__);
            }

            $request  = $this->getRequest($action);
            $response = $this->getResponse($action);

            assert($this->log4request($request));
            $this->$method($request, $response, ...$arguments);
            $response->confirm();
            assert($this->log4response($response));
        } catch (\Exception $exc) {
            $response = $this->handleException($exc, $this->response);
        }

        return $response->regress();
    }

    public function __get($name) {
        return $this->container->get($name);
    }

    protected function handleException(\Exception $exc, SlimResponse $response) {
        return ErrorResponse::handleException($exc, $response);
    }

    protected function log4request($request) {
        $this->logger->info("receive request:$request", ['controller']);
        return true;
    }

    protected function log4response($response) {
        $this->logger->info("send response:$response", ['controller']);
        return true;
    }

    /**
     *
     * @param string $action
     * @return Request
     */
    protected function getRequest($action) {
        $class = get_called_class() . "\\{$action}Request";
        $request = new $class($this->request->getParams(), $this->request);
        /* @var $request Request */
        $request->takeDependencies($this->container);
        $request->confirm();
        return $request;
    }

    /**
     *
     * @param string $action
     * @return Response
     */
    protected function getResponse($action) {
        $class      = get_called_class() . "\\{$action}Response";
        $response   = new $class([], $this->response);
        /* @var $response Response */
        $response->takeDependencies($this->container);
        return $response;
    }

}
