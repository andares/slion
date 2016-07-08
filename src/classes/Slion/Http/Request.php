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
abstract class Request extends Meta {
    /**
     *
     * @var SlimRequest
     */
    private $request;

    public function __construct(array $data, SlimRequest $request) {
        parent::__construct(array_merge($request->getParams(), $data));

        // 取出对应的参数
        $this->request = $request;
    }

    public function __call($name, $arguments) {
        return $this->request->$name(...$arguments);
    }

    public function __debugInfo() {
        return $this->toArray();
    }
}
