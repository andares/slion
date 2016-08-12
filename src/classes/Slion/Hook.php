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

    public function add(string $name, callable $taker = null): self {
        if (!$taker) {
            $taker = function(array $handlers, Run $run, ...$args) {
                foreach ($handlers as $handler) {
                    $handler($run, ...$args);
                }
            };
        }
        $this->takers[$name] = $taker;

        return $this;
    }

    public function attach(string $name, callable $handler): self {
        $this->handlers[$name][] = $handler;

        return $this;
    }

    public function take(string $name, ...$args) {
        global $run;
        /* @var $run \Slion\Run */

        if (!isset($this->takers[$name]) || !isset($this->handlers[$name])) {
            return;
        }

        $taker = $this->takers[$name];
        $taker($this->handlers[$name], $run, ...$args);
    }
}
