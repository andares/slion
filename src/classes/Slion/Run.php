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
     * @var string
     */
    private $_id;

    /**
     *
     * @var \Slim\App
     */
    private $_app = null;

    /**
     *
     * @var \Slim\Container
     */
    private $_container = null;

    /**
     *
     * @var array
     */
    private $_settings = [];

    /**
     *
     * @var Init[]
     */
    private $_extensions;

    /**
     *
     * @var string
     */
    private $_current_extension;

    /**
     *
     * @var array
     */
    private $_bootstrappers;

    /**
     *
     * @var array
     */
    private $_skips  = [];

    /**
     *
     * @var []
     */
    private static $_imported = [];

    public function __construct() {
        $GLOBALS['run'] = $this;

        $this->_id = $this->genId();

        // 初始化imported
        foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $dir) {
            self::$_imported[$dir] = 1;
        }
    }

    /**
     *
     * @return string
     */
    private function genId(): string {
        $generator = new Utils\IdGenerator;
        $generator->algo = 'fnv1a32';
        return $generator->prepare(microtime())->hash_hmac('triss')
            ->gmp_strval()->get();
    }

    /**
     *
     * @return string
     */
    public function getId(): string {
        return $this->_id;
    }

    /**
     *
     * @param string $name
     * @return type
     */
    public function __get(string $name) {
        return $this->_container->has($name) ?
            $this->_container->get($name) : null;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value) {
        $this->_container[$name] = $value;
    }

    /**
     *
     * @return \Slim\App
     */
    public function app(): \Slim\App {
        return $this->_app;
    }

    /**
     *
     * @return \Slim\Container
     */
    public function container(): \Slim\Container {
        return $this->_container;
    }

    /**
     *
     * @return mixed
     */
    public function settings(string $key = null) {
        return $key ? ($this->_settings[$key] ?? null) : $this->_settings;
    }

    /**
     *
     * @param string $extension_name
     * @return string
     */
    public function root(string $extension_name): string {
        return $this->_extensions[$extension_name]['root'];
    }

    /**
     * 框架准备
     */
    public function ready(array $container) {
        // 构建自身
        $this->_app          = new \Slim\App($container);
        $this->_container    = $this->_app->getContainer();
        $this->_settings     = $this->_container->get('slion_settings');

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
        $this->_current_extension            = $name;
        $this->_extensions[$name]['root']    = $root;
        return $this;
    }

    /**
     *
     * @param string $name
     * @return self
     */
    public function select(string $name): self {
        $this->_current_extension    = $name;
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
        $this->_extensions[$this->_current_extension]['boots'][$seq][] = $boot;
        $this->_bootstrappers[$seq][$this->_current_extension][]       = $info;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getBootstrappers() {
        return $this->_bootstrappers;
    }

    /**
     * 初始化环境并返回app
     * @return \Slim\App
     */
    public function __invoke() {
        ksort($this->_bootstrappers);
        foreach ($this->_bootstrappers as $seq => $list) {
            if (isset($this->_skips[$seq])) {
                continue;
            }
            foreach ($list as $extension_name => $info) {
                $extension = $this->_extensions[$extension_name];
                foreach ($extension['boots'][$seq] as $boot) {
                    $boot->call($this, $extension['root']);
                }
            }
        }

        return $this->_app;
    }

    /**
     * 跳过某些引导序列
     * @param int $seq
     */
    public function skip(int $seq) {
        $this->_skips[$seq] = 1;
    }

    /**
     * 注册自动载入
     */
    private function registerAutoload() {
        spl_autoload_register(function ($classname) {
            try {
                foreach (self::$_imported as $dir => $flag) {
                    $file = $dir . DIRECTORY_SEPARATOR .
                        \str_replace("\\", DIRECTORY_SEPARATOR, $classname) . ".php";
                    if (file_exists($file)) {
                        include $file;
                    }
                }
            } catch (\Throwable $exc) {
                error_log($exc->getMessage());
                error_log($exc->getCode());
                error_log($exc->getTraceAsString());
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
    private function importLibrary(string $dir): array {
        if (!isset(self::$_imported[$dir])) {
            ini_set('include_path', $dir . PATH_SEPARATOR . ini_get('include_path'));
            self::$_imported[$dir] = 1;
        }
        return self::$_imported;
    }

    /**
     * php ini检测
     * @param array $setup_list
     * @param array $check_list
     * @throws \RuntimeException
     */
    private function phpIniReady(array $setup_list = [], array $check_list = []) {
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
