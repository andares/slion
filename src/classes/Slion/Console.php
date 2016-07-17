<?php

namespace Slion;

/**
 * Description of Console
 *
 * 提供几种注册指令的方案：
 * 1、map()，给目录和命名空间，搜索该目录以及其下Commands目录中的所有继承Commands的类
 * 2、add()，直接注册一个对象
 *
 * @author andares
 */
class Console {
    public static $default_map = [];

    protected $name = '';

    protected $default_domain = '';

    protected $style = null;

    /**
     *
     * @var Console\Command[]
     */
    protected $commands = [];

    protected $base;

    /**
     *
     * @var array
     */
    protected $index = [];

    public function __construct(string $name = '', bool $without_default_map = false) {
        $name && $this->name = $name;

        // 默认map
        if (!$without_default_map) {
            foreach (static::$default_map as $map) {
                $this->map(...$map);
            }
        }
        return $this->style;
    }

    public function __invoke(array $argv): string {
        $this->base = array_shift($argv);
        if (!$argv) {
            $this->getBaseHelp();
            $this->printCommandsList(str_repeat(' ', 3), $this->getCommandsList());
            return '';
        }

        $domain = array_shift($argv);
        try {
            // 基本域
            return $this->callCommand($domain, $argv);
        } catch (\DomainException $exc) {
            try {
                // 尝试默认域
                $domain = "$this->default_domain.$domain";
                return $this->callCommand($domain, $argv);
            } catch (\DomainException $exc) {
                throw new \BadFunctionCallException(
                    chord_tr('console/errors', 'cmd_not_found', $domain));
            }
        }
    }

    protected function callCommand($domain, array $argv): string {
        // 域指令
        $cmd    = $this->commands[$domain] ?? null;
        if ($cmd) {
            return $cmd($argv);
        }
        // 域节点
        if (isset($this->index[$domain])) {
            $this->getBaseHelp($domain);
            $this->printCommandsList(str_repeat(' ', 3), $this->getCommandsListByNode($domain));
            return '';
        }
        throw new \DomainException('domain is not found');
    }

    protected function wrapDefaultDomain($domain) {
        return preg_replace("/^($this->default_domain\.)(.*)$/", '[$1]$2', $domain);
    }

    public function getCommandsListByNode(string $node) {
        foreach ($this->index[$node] as $domain) {
            foreach ($this->commands[$domain]->getActions() as $action => $arguments) {
                yield $domain => "$action $arguments";
            }
        }
    }

    public function getCommandsList(...$white_list): \Generator {
        if ($white_list) {
            foreach ($white_list as $domain) {
                foreach ($this->commands[$domain]->getActions() as $action => $arguments) {
                    yield $domain => "$action $arguments";
                }
            }
        } else {
            foreach ($this->commands as $domain => $command) {
                foreach ($command->getActions() as $action => $arguments) {
                    yield $domain => "$action $arguments";
                }
            }
        }
    }

    public function getBaseHelp(string $domain = '') {
        $domain = $domain ? $this->wrapDefaultDomain($domain) : '<domain>';
        $this->ec("- example: $this->base $domain <action> [<arguments>..]");
        $this->ec();
        $this->ec('- commands list:');
    }

    public function printCommandsList(string $prefix, \Generator $list) {
        $current = '';
        foreach ($list as $domain => $line) {
            // 给默认域加可选
            $domain = $this->wrapDefaultDomain($domain);
            if ($current != $domain) {
                $this->ec();
                $current = $domain;
            }

            $this->ec("$prefix $domain $line");
        }
    }

    public function setDefaultDomain(string $domain) {
        $this->default_domain = $domain;
    }

    public function getStyle() {
        if (!$this->style) {
            $this->style = new Console\Style;
        }
        return $this->style;
    }

    public function map(string $dir, string $space) {
        $search = [
            $space              => $dir,
            "$space\\Commands"  => $dir . DIRECTORY_SEPARATOR . 'Commands',
        ];

        foreach ($search as $space => $dir) {
            if (!file_exists($dir)) {
                continue;
            }

            $it = new \RecursiveDirectoryIterator($dir);
            foreach ($it as $file) {
                /* @var $file \SplFileInfo */
                if ($file->isDir()) {
                    continue;
                }
                if ($file->getExtension() != 'php') {
                    continue;
                }

                $class  = $space . '\\' . $file->getBasename('.php');
                if (!class_exists($class)) {
                    continue;
                }

                $refl   = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }
                if ($this->isCommand($refl)) {
                    $this->add(new $class($this));
                }
            }
        }
    }

    private function isCommand(\ReflectionClass $refl) {
        $parent = $refl->getParentClass();
        if (!$parent) {
            return false;
        }
        if ($parent->getName() == (__NAMESPACE__ . '\\Console\\Command')) {
            return true;
        }
        return $this->isCommand($parent);
    }

    /**
     * 添加一个命令。
     *
     * 如果在同一域下重复添加，后者不会生效。
     *
     * @param \Slion\Console\Command $command
     */
    public function add(Console\Command $command) {
        $domain = $command->getDomain();
        if (!$domain || isset($this->commands[$domain])) {
            return;
        }
        $this->commands[$domain] = $command;

        $this->buildCommandsIndex($command);
    }

    private function buildCommandsIndex(Console\Command $command) {
        $domain = $command->getDomain();
        $path   = [];
        foreach (explode('.', $domain) as $node) {
            $path[]  = $node;
            $key     = implode('.', $path);
            $domain != $key && $this->index[$key][] = $domain;
        }
    }

    public function ec(string $words = '', $level = 'info', Console\Style $style = null) {
        !$style && $style = $this->getStyle();
        echo $style->$level($words), PHP_EOL;
    }
}
