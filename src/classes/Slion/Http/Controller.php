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
     *
     * @param string $name
     * @param array $arguments
     * @return \Slion\Http\Response
     * @throws \BadMethodCallException
     */
    public function __call(string $name, array $arguments): Response {
        // 生成参数对象
        $receives = $this->getReceives($name);

        lg($receives[1]->confirm()->toLog());
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
        lg($response->confirm()->toLog());
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
