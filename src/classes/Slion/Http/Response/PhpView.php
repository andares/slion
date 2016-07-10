<?php

namespace Slion\Http\Response;

use Slion\Http\Response;
use Slion\Http\DependenciesTaker;
use Slim\Http\Response as RawResponse;

/**
 * Description of ResponseView
 *
 * @author andares
 */
abstract class PhpView extends Response implements DependenciesTaker {
    protected $_template = 'index.phtml';

    /**
     *
     * @var \Slim\Views\PhpRenderer
     */
    private $renderer;

    public function regress(RawResponse $response) {
        return $this->renderer->render($response, $this->_template, $this->toArray());
    }

    public function setTemplate($template) {
        $this->_template = $template;
    }

    public function takeDependencies(\Slim\Container $container) {
        $this->renderer = $container['renderer'];
        if (!($this->renderer instanceof \Slim\Views\PhpRenderer)) {
            throw new \RuntimeException("require Slim Php-View to render page");
        }
    }
}
