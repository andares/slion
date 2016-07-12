<?php

namespace Slion;

/**
 * Description of Hook
 *
 * @author andares
 */
class Hook {
    /**
     *
     * @var \Slim\Container
     */
    private $container;

    /**
     *
     * @var callable[]
     */
    private $takers = [];

    /**
     *
     * @var callable[]
     */
    private $handlers = [];

    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    public function add(string $name, callable $taker) {
        $this->takers[$name] = $taker;
    }

    public function attach(string $name, callable $handler) {
        $this->handlers[$name][] = $handler;
    }

    public function take(string $name, $caller, ...$args) {
        if (!isset($this->takers[$name]) || !isset($this->handlers[$name])) {
            return;
        }

        $taker = $this->takers[$name];
        $taker($this->handlers, $caller, $this->container, ...$args);
    }
}
