<?php

namespace Slion;

/**
 * Description of Run
 *
 * @author andares
 */
class Run {
    /**
     *
     * @var \Slim\App
     */
    private $app;

    /**
     *
     * @var \Slim\Container
     */
    private $container;

    /**
     *
     * @var array
     */
    private $settings;

    /**
     *
     * @var Init[]
     */
    private $extensions;

    /**
     *
     * @var string
     */
    private $current_extension;

    /**
     *
     * @var array
     */
    private $bootstrappers;

    /**
     *
     * @var array
     */
    private $skips  = [];

    /**
     *
     * @var []
     */
    private static $imported = [];

    /**
     *
     * @param \Slim\App $app
     */
    public function __construct(\Slim\App $app) {
        $this->app          = $app;
        $this->container    = $app->getContainer();
        $this->settings     = $this->container->get('slion_settings');
    }

    /**
     *
     * @param string $name
     * @return type
     */
    public function __get(string $name) {
        return $this->container->get($name);
    }

    /**
     *
     * @return \Slim\App
     */
    public function app(): \Slim\App {
        return $this->app;
    }

    /**
     *
     * @return \Slim\Container
     */
    public function container(): \Slim\Container {
        return $this->container;
    }

    /**
     *
     * @return array
     */
    public function settings(): array {
        return $this->settings;
    }

    /**
     *
     * @param string $extension_name
     * @return string
     */
    public function root(string $extension_name): string {
        return $this->extensions[$extension_name]['root'];
    }

    /**
     * 框架准备
     */
    public function ready() {
        $GLOBALS['app'] = $this->app;
        $GLOBALS['run'] = $this;

        // 注册自动载入
        $this->registerAutoload();
    }

    /**
     * 添加一个扩展
     * @param string $name
     * @param string $root
     * @return self
     */
    public function add(string $name, string $root): self {
        $this->current_extension            = $name;
        $this->extensions[$name]['root']    = $root;
        return $this;
    }

    /**
     *
     * @param string $name
     * @return self
     */
    public function select(string $name): self {
        $this->current_extension    = $name;
        return $this;
    }

    /**
     *
     * 设置引导
     *
     * @param int $seq
     * @param \Slion\callable $boot
     * @param string $info
     * @return \self
     */
    public function setup(int $seq, callable $boot, string $info = ''): self {
        $this->extensions[$this->current_extension]['boots'][$seq][] = $boot;
        $this->bootstrappers[$seq][$this->current_extension][]       = $info;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getBootstrappers() {
        return $this->bootstrappers;
    }

    /**
     * 初始化环境并返回app
     * @return \Slim\App
     */
    public function __invoke() {
        ksort($this->bootstrappers);
        foreach ($this->bootstrappers as $seq => $list) {
            if (isset($this->skips[$seq])) {
                continue;
            }
            foreach ($list as $extension_name => $info) {
                $extension = $this->extensions[$extension_name];
                foreach ($extension['boots'][$seq] as $boot) {
                    $boot($extension['root'],
                        $this->app,
                        $this->container,
                        $this->settings,
                        $this);
                }
            }
        }

        return $this->app;
    }

    /**
     * 跳过某些引导序列
     * @param int $seq
     */
    public function skip(int $seq) {
        $this->skips[$seq] = 1;
    }

    /**
     * 注册自动载入
     */
    public function registerAutoload() {
        spl_autoload_register(function ($classname) {
            $classname  = \str_replace("\\", DIRECTORY_SEPARATOR, $classname);

            try {
                include "$classname.php";
            } catch (\Throwable $exc) {
                return false;
            }
            if (class_exists($classname) || interface_exists($classname) ||
                trait_exists($classname)) {
                return true;
            }
            return false;
        });
    }

    /**
     * 导入库
     * @param string $dir
     * @return array
     */
    public function importLibrary(string $dir): array {
        if (!isset(self::$imported[$dir])) {
            ini_set('include_path', $dir . PATH_SEPARATOR . ini_get('include_path'));
            self::$imported[$dir] = 1;
        }
        return self::$imported;
    }

    /**
     * php ini检测
     * @param array $setup_list
     * @param array $check_list
     * @throws \RuntimeException
     */
    public function phpIniReady(array $setup_list = [], array $check_list = []) {
        foreach ($setup_list as $name => $value) {
            ini_set($name, $value);
        }
        foreach ($check_list as $name => $value) {
            if (ini_get($name) != $value) {
                throw new \RuntimeException("PHP ini [$name] should be $value");
            }
        }
    }
}
