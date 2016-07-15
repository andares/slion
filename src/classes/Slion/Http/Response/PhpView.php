<?php

namespace Slion\Http\Response;

use Slion\Http\Response;
use Slion\Components\DependenciesTaker;
use Slim\Http\Response as RawResponse;

/**
 * Description of ResponseView
 *
 * @author andares
 */
abstract class PhpView extends Response {
    protected $_template = 'index.phtml';

    /**
     *
     * @var \Slim\Views\PhpRenderer
     */
    private $renderer;

    /**
     * @todo 这里要待扩展成 view error response
     * @param \Exception $exc
     * @param \Slim\Container $container
     * @return \self
     */
    public function raiseError(\Exception $exc, \Slim\Container $container): self {
        return ErrorResponse::handleException($exc, $container);
    }

    public function regress(RawResponse $response) {
        return $this->renderer->render($response, $this->_template, $this->toArray());
    }

    public function setTemplate($template) {
        $this->_template = $template;
    }

    public function takeDependencies(\Slim\Container $container) {
        parent::takeDependencies($container);

        $this->renderer = $container['renderer'];
        if (!($this->renderer instanceof \Slim\Views\PhpRenderer)) {
            throw new \RuntimeException("require Slim Php-View to render page");
        }
    }
}
