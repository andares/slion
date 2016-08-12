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
     * @var RawRequest
     */
    protected $raw_request;

    /**
     *
     * @var RawResponse
     */
    protected $raw_response;

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
        return static::$class . '\\' . ucfirst($action);
    }

    /**
     *
     * @param RawRequest $raw
     * @param string $prefix
     * @return \Slion\Http\Request
     */
    protected function makeRequest(RawRequest $raw, string $prefix = ''): Request {
        $class  = "{$prefix}Request";
        if (@class_exists($class)) {
            $request = new $class($raw, $this);
        } else {
            $request = $this->makeDefaultRequest($raw);
        }
        /* @var $request Request */
        return $request;
    }

    /**
     *
     * @param RawRequest $raw
     * @return \Slion\Http\Request
     */
    protected function makeDefaultRequest(RawRequest $raw): Request {
        return new Request($raw, $this);
    }

    /**
     *
     * @param RawResponse $raw
     * @param string $prefix
     * @return \Slion\Http\Response
     * @throws \BadMethodCallException
     */
    protected function makeResponse(RawResponse $raw,
        string $prefix = ''): Response {

        $class  = "{$prefix}Response";
        if (@class_exists($class)) {
            $response = new $class($raw, $this);
        } else {
            $response = $this->makeDefaultResponse($raw);
        }
        /* @var $response Response */
        return $response;
    }

    /**
     *
     * @param RawResponse $raw
     * @return \Slion\Http\Response
     */
    protected function makeDefaultResponse(RawResponse $raw): Response {
        return new Response($raw, $this);
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
        $receives = $this->getReceives($name);

        dlog($receives[1]->confirm()->toLog());
        $this->hook->take(\Slion\HOOK_BEFORE_ACTION, $this, $name,
            ...$receives, ...$arguments);

        // 执行业务
        $action = ucfirst($name);
        $method = "action$action";
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("action [$action] is not exist");
        }
        $this->$method(...$receives, ...$arguments);

        $response = $receives[0];
        $this->hook->take(\Slion\HOOK_BEFORE_RESPONSE, $response);
        $this->setCookieHeaders($response);
        dlog($response->confirm()->toLog());
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
        $request    = $this->makeRequest($this->raw_request,    $prefix);
        array_unshift($receives, $request);
        $response   = $this->makeResponse($this->raw_response,  $prefix);
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
