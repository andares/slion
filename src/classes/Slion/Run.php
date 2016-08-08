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
    private $packages;

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
     * 框架准备
     */
    public function prepare() {
        $GLOBALS['app'] = $this->app;
        $GLOBALS['run'] = $this;

        // 注册自动载入
        $this->registerAutoload();
    }

    /**
     * 设置package
     * @param \Slion\Init $init
     */
    public function setup($name, Init $init) {
        $this->packages[$name] = $init;
    }

    /**
     * 生成环境
     * @return \Slim\App
     */
    public function __invoke() {
        foreach ($this->packages as $name => $init) {
            $init->head($this->app, $this->container, $this->settings);
        }
        foreach ($this->packages as $name => $init) {
            $init->tail($this->app, $this->container, $this->settings);
        }

        return $this->app;

//        \Slion\Init::dispatchByRoutes($app, cf('routes'),
//            function(\Slim\App $app, string $module, string $space) {
//
//            $app->any("/$module/{controller}[/{action}[/{more:.*}]]",
//                function ($request, $response, $args) use ($space) {
//
//                $dispatcher = new \LionCommon\Http\Dispatcher($space, $this, $request, $response);
//                return $dispatcher->route($args['controller'], $args['action'] ?? 'index',
//                    explode('/', $request->getAttribute('more')));
//            })->setName($module);
//        });
    }

    public static function dispatchByRoutes(\Slim\App $app, array $routes, callable $register = null) {
        if (!$register) {
            $register = function(\Slim\App $app, string $module, string $space) {
                $app->any("/$module/{controller}[/{action}[/{more:.*}]]",
                    function ($request, $response, $args) use ($space) {

                    $dispatcher = new Http\Dispatcher($space, $this, $request, $response);
                    return $dispatcher->route($args['controller'], $args['action'] ?? 'index',
                        explode('/', $request->getAttribute('more')));
                })->setName($module);
            };
        }

        foreach ($routes as $module => $space) {
            $register($app, $module, $space);
        }
    }

    private function registerAutoload() {
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
}
