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

    public function __call($name, $arguments) {
        return $this->console->$name(...$arguments);
    }

    public function __invoke(array $argv) {
        $this->init();

        if (!$argv) {
            $this->getBaseHelp($this->domain);
            $this->printCommandsList(str_repeat(' ', 3), $this->getCommandsList($this->domain));
            return '';
        }

        $action     = array_shift($argv);
        $arguments  = $this->getArguments($action, $argv);

        return '';
    }

    protected function getArguments(string $action, array $argv): Arguments {
        if (!isset($this->actions[$action])) {
            throw new \BadMethodCallException(
                chord_tr('console/errors', 'action_not_found', $this->domain, $action));
        }
        $argumens = new Arguments($this->actions[$action]);
        return $argumens($argv);
    }

    public function init() {}

    public function getDomain() {
        return $this->domain;
    }
}
