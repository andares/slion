<?php

namespace Slion;
use Slim\Container;

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
    /**
     *
     * @var Container
     */
    protected $container;

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

    public function __construct(Container $container, string $name = '') {
        $this->container = $container;
        $name && $this->name = $name;
    }

    public function __invoke(array $argv): string {
        $this->base = array_shift($argv);
        if (!$argv) {
            $this->makeHelp();
            return '';
        }

        $domain = array_shift($argv);
        $action = $argv ? array_shift($argv) : '';
        if (!$action) {
            // 处理帮助
            $this->makeHelp($domain);
            return '';
        }

        // 执行
        return $this->callCommand($domain, $argv);
    }

    protected function makeHelp(string $domain = '') {
        // 域指令的帮助功能
        if ($domain) {
            $command = $this->commands[$domain] ??
                ($this->commands["$this->default_domain.$domain"] ?? null);
            /* @var $command Console\Command */
            if ($command) {
                $help_text = $command->getHelp();
                if ($help_text) {
                    $this->ec($help_text);
                    $this->ec();
                }
            }
        } else {
            $this->ec(">>> Welcome to use Chord <<<", 'clear');
            $this->ec();
        }

        // 固定内容
        $this->ec("- example: $this->base <domain> <action> [<arguments>..]");
        $this->ec();
        $this->ec('- commands list:');

        // 输出指令列表
        $prefix = str_repeat(' ', 3) . $this->base;
        if ($domain) {
            $list = $this->getActionHelpList($domain);
            $this->printActionHelpList($prefix, $list);

            if (!$list) {
                throw new \DomainException(
                    tr('console/errors', 'domain_not_found', $domain));
            }
        } else {
            $this->printActionHelpList($prefix, $this->getActionHelpList());
        }
    }

    public function __get($name) {
        return $this->container->get($name);
    }

    protected function callCommand(string $domain, string $action, array $argv): string {
        // 域指令
        $cmd    = $this->commands[$domain] ?? null;
        if (!$cmd) {
            // 检查默认
            $domain = "$this->default_domain.$domain";
            $cmd    = $this->commands[$domain] ?? null;
            if (!$cmd) {
                throw new \BadFunctionCallException(
                    tr('console/errors', 'cmd_not_found', $domain));
            }
        }
        return $cmd($action, $argv);
    }

    public function getActionHelpList(...$white_list): array {
        $result = [];
        if ($white_list) {
            foreach ($white_list as $domain) {
                if (isset($this->commands[$domain])) {
                    foreach ($this->commands[$domain]->getActions() as $action => $arguments) {
                        $result[$domain] = "$action $arguments";
                    }
                }
                if (isset($this->commands["$this->default_domain.$domain"])) {
                    $default_domain = "$this->default_domain.$domain";
                    foreach ($this->commands[$default_domain]
                        ->getActions() as $action => $arguments) {
                        $result[$default_domain] = "$action $arguments";
                    }
                }

                $node = $domain;
                foreach ($this->getActionHelpListByNode($node) as $domain => $help) {
                    $result[$domain] = $help;
                }
            }
        } else {
            foreach ($this->commands as $domain => $command) {
                foreach ($command->getActions() as $action => $arguments) {
                    $result[$domain] = "$action $arguments";
                }
            }
        }
        return $result;
    }

    private function getActionHelpListByNode(string $node): \Generator {
        if (isset($this->index[$node])) {
            foreach ($this->index[$node] as $domain) {
                foreach ($this->commands[$domain]->getActions() as $action => $arguments) {
                    yield $domain => "$action $arguments";
                }
            }
        }
        if (isset($this->index["$this->default_domain.$node"])) {
            $default_node = "$this->default_domain.$node";
            foreach ($this->index[$default_node] as $domain) {
                foreach ($this->commands[$domain]->getActions() as $action => $arguments) {
                    yield $domain => "$action $arguments";
                }
            }
        }
    }

    public function printActionHelpList(string $prefix, array $list) {
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
        $this->ec();
    }

    protected function wrapDefaultDomain($domain) {
        return preg_replace("/^($this->default_domain\.)(.*)$/", '[$1]$2', $domain);
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
