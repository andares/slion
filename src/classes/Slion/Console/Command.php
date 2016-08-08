<?php

namespace Slion\Console;


/**
 * Description of Command
 *
 * @author andares
 */
class Command {
    protected $domain   = "";

    protected $actions  = [];

    /**
     *
     * @var \Slion\Console
     */
    protected $console;

    final public function __construct(\Slion\Console $console) {
        $this->console = $console;
    }

    public function getActions() {
        return $this->actions;
    }

    public function __invoke($action, array $argv) {
        if (!$this->init()) {
            return '';
        }

        $arguments  = $this->getArguments($action, $argv);

        // 调用action
        return '';
    }

    protected function getArguments(string $action, array $argv): Arguments {
        if (!isset($this->actions[$action])) {
            throw new \BadMethodCallException(
            tr('console/errors', 'action_not_found', $this->domain, $action));
        }
        $argumens = new Arguments($this->actions[$action]);
        return $argumens($argv);
    }

    public function init(): bool {
        return true;
    }

    public function getHelp(): string {
        return '';
    }

    public function getDomain() {
        return $this->domain;
    }
}
