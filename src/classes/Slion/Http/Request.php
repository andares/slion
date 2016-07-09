<?php
namespace Slion\Http;

use Slion\Utils\Meta;
use Slim\Http\Request as SlimRequest;


/**
 * Http请求
 *
 * @author andares
 *
 */
abstract class Request extends Meta implements DependenciesTaker {
    /**
     *
     * @var SlimRequest
     */
    protected $_request;

    public function __construct(array $data, SlimRequest $request) {
        parent::__construct(array_merge($request->getParams(), $data));
        $this->_request     = $request;
    }

    public function __call($name, $arguments) {
        return $this->_request->$name(...$arguments);
    }

    public function __debugInfo() {
        return $this->toArray();
    }

    public function takeDependencies(\Slim\Container $container) {}
}
