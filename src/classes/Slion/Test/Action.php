<?php

namespace Slion\Test;
use Slion\Http;
use Slim\Http\Request as RawRequest;

/**
 * Description of Action
 *
 * @author andares
 */
class Action {
    /**
     *
     * @var Http\Controller
     */
    protected $controller;

    /**
     *
     * @var string
     */
    protected $action;

    protected $request = [];
    protected $cookies = [];

    /**
     *
     * @param \Slion\Http\Controller $controller
     * @param string $action
     */
    public function __construct(Http\Controller $controller,
        string $action) {

        $this->controller = $controller;
        $this->action     = $action;

        $this->restore();
    }

    /**
     *
     * @param array $data
     * @return \self
     */
    public function request(array $data): self {
        $modifier = function() use ($data) {
            $this->raw_request = $this->raw_request->withQueryParams($data);
        };
        $modifier->call($this->controller);

        return $this;
    }

    /**
     *
     * @param array $data
     * @param bool $clear_response
     * @return \self
     */
    public function cookies(array $data, bool $clear_response = true): self {
        $modifier = function() use ($data, $clear_response) {
            $this->requestCookies = $data;
            $clear_response && $this->responseCookies = [];
        };
        $modifier->call($this->controller->cookies);

        return $this;
    }

    /**
     *
     * @return \Slion\Http\Response
     */
    public function call(): Http\Response {
        $response = $this->controller->{$this->action}();
        return $response;
    }

    /**
     *
     * @return self
     */
    public function restore(): self {
        $this->cookies([]);
        return $this;
    }
}
