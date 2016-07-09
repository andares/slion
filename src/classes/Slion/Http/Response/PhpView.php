<?php

namespace Slion\Http\Response;

use Slion\Http\Response;
use Slion\Http\DependenciesTaker;

/**
 * Description of ResponseView
 *
 * @author andares
 */
abstract class PhpView extends Response implements DependenciesTaker {
    protected $_template = 'index.phtml';

    protected $_http_headers = [
    ];

    /**
     *
     * @var \Slim\Views\PhpRenderer
     */
    private $renderer;

    public function regress() {
        $response = $this->_response;

        // http 头
        if ($this->_http_headers) {
            foreach ($this->_http_headers as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }

        // 渲染
        return $this->renderer->render($response, $this->_template, $this->toArray());
    }

    public function setTemplate($template) {
        $this->_template;
    }

    public function setHeaders(array $headers = [], $reset = false) {
        $reset && $this->_http_headers = [];

        foreach ($headers as $name => $value) {
            $this->_http_headers[$name] = $value;
        }
    }

    public function takeDependencies(\Slim\Container $container) {
        $this->renderer = $container['renderer'];
        if (!($this->renderer instanceof \Slim\Views\PhpRenderer)) {
            throw new \RuntimeException("require Slim Php-View to render page");
        }
    }
}
