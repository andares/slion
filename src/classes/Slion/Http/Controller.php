<?php
namespace Slion\Http;
use Slim\Container;
use Slim\Http\{
    Cookies,
    Request as RawRequest,
    Response as RawResponse};
use Slion\Utils;

/**
 * 默认控制器
 *
 * @author andares
 *
 * @property Utils\Config $config
 * @property Utils\Dict $dict
 * @property Utils\Logger $logger
 * @property \Slion\Hook $hook
 * @property Cookies $cookies
 */
abstract class Controller {
//

    /**
     *
     * @var Container
     */
    protected $container;

    /**
     *
     * 注意，$this->raw_request与从容器中取出的$this->request是不同的，后者未经过中件间的加工
     *
     * @var RawRequest
     */
    protected $raw_request;

    /**
     * 注意，$this->raw_response与从容器中取出的$this->response是不同的，后者未经过中件间的加工
     *
     * @var RawResponse
     */
    protected $raw_response;

    /**
     *
     * @var array
     */
    protected $receives;

    /**
     *
     * @var array
     */
    protected $more_args = [];

    /**
     *
     * @param Container $container
     */
    public function __construct(Container $container = null,
        RawRequest $raw_request = null,
        RawResponse $raw_response = null) {

        global $run;
        /* @var $run \Slion\Run */
        $this->container = $container ? $container : $run->container();

        $this->raw_request  = $raw_request  ? $raw_request  : $this->request;
        $this->raw_response = $raw_response ? $raw_response : $this->response;
    }

    /**
     *
     * @param RawRequest $request
     * @return array
     */
    protected function getMore(RawRequest $request): array {
        $more = $request->getAttribute('more');
        return $more ? explode('/', $more) : [];
    }

    /**
     *
     * @param string $action
     * @return string
     */
    protected function genPrefix(string $action): string {
        return static::class . '\\' . ucfirst($action);
    }

    /**
     *
     * @param string $prefix
     * @return \Slion\Http\Request
     */
    protected function makeRequest(string $prefix = ''): Request {
        $class  = "{$prefix}Request";
        if (@class_exists($class)) {
            $request = new $class($this->raw_request, $this->raw_response);
        } else {
            $request = $this->makeDefaultRequest();
        }
        /* @var $request Request */
        return $request;
    }

    /**
     *
     * @return \Slion\Http\Request
     */
    protected function makeDefaultRequest(): Request {
        return new Request($this->raw_request, $this->raw_response);
    }

    /**
     *
     * @param string $prefix
     * @return \Slion\Http\Response
     * @throws \BadMethodCallException
     */
    protected function makeResponse(string $prefix = ''): Response {

        $class  = "{$prefix}Response";
        if (@class_exists($class)) {
            $response = new $class($this->raw_response, $this->raw_request);
        } else {
            $response = $this->makeDefaultResponse();
        }
        /* @var $response Response */
        return $response;
    }

    /**
     *
     * @return \Slion\Http\Response
     */
    protected function makeDefaultResponse(): Response {
        return new Response($this->raw_response, $this->raw_request);
    }

    /**
     * @todo 在升到php 7.1之前，任其为null报错
     * @return \Slion\Http\Request
     */
    public function request(): Request {
        return $this->receives[1] ?? null;
    }

    /**
     * @todo 在升到php 7.1之前，任其为null报错
     * @return \Slion\Http\Response
     */
    public function response(): Response {
        return $this->receives[0] ?? null;
    }

    /**
     *
     * @param string $name
     * @param array $arguments
     * @return \Slion\Http\Response
     * @throws \BadMethodCallException
     */
    public function __call(string $name, array $arguments): Response {
        // 生成参数对象
        $this->receives = $this->getReceives($name);

        lg($this->request()->confirm()->toLog());
        $this->hook->take(\Slion\HOOK_BEFORE_ACTION, $this, $name,
            ...$this->receives, ...$arguments);

        // 执行业务
        $action = ucfirst($name);
        $method = "action$action";
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("action [$action] is not exist");
        }
        $this->$method(...$this->receives, ...$arguments);

        $response = $this->response();
        $this->setCookieHeaders($response);
        lg($response->confirm()->toLog());
        $this->hook->take(\Slion\HOOK_BEFORE_RESPONSE, $response);
        return $response;
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        return $this->container->get($name);
    }

    /**
     *
     * @param string $action
     * @return array
     */
    protected function getReceives(string $action): array {
        $receives   = $this->getMore($this->raw_request);

        $prefix     = $this->genPrefix($action);
        $request    = $this->makeRequest($prefix);
        array_unshift($receives, $request);
        $response   = $this->makeResponse($prefix);
        array_unshift($receives, $response);
        return $receives;
    }

    /**
     *
     */
    protected function setCookieHeaders(Response $response) {
        $set_cookies = $this->cookies->toHeaders();
        if ($set_cookies) {
            $response->setHeaders([
                'Set-Cookie'    => implode('; ', $set_cookies),
            ]);
        }
    }
}
