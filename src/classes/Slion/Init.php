<?php
namespace Slion;

use Slim\Container;
use Tracy\Debugger;

/**
 * Description of Autoload
 *
 * @author andares
 */
class Init {
    /**
     * @todo 存在优化可能
     * @todo slim中使用了closure的bindTo()方法，可以改进为php7的call()方法，效率更高
     * @param \Slim\App $app
     * @param array $routes
     * @param \Slion\callable $register
     */
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

    public static function registerAutoload(array $libraries, callable $autoload = null) {
        if ($autoload) {
            spl_autoload_register($autoload);
        } else {
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

        // 自动导入配置中的
        foreach ($libraries as $dir) {
            self::importLibrary($dir);
        }

        return true;
    }

    public static function importLibrary($dir) {
        static $imported = [];

        if (!isset($imported[$dir])) {
            ini_set('include_path', $dir . PATH_SEPARATOR . ini_get('include_path'));
            $imported[$dir] = 1;
        }
        return $imported;
    }

    public static function tracySetup(array $settings, Utils\Logger $logger,
        $display_error_details = false) {

        // 设定模式
        if (isset($settings['is_prod'])) {
            $is_prod = $settings['is_prod'];
        } else {
            $is_prod = $display_error_details ? Debugger::DEVELOPMENT : Debugger::PRODUCTION;
        }

        // 创建debugger
        Debugger::enable($is_prod, $logger->directory, $logger->email);
        Debugger::setLogger($logger);

        // 配置
        Debugger::$maxDepth     = $settings['max_depth'];
        Debugger::$maxLength    = $settings['max_length'];
        Debugger::$strictMode   = $settings['strict_mode'];
        Debugger::$scream       = $settings['scream'];
    }

    public static function debuggerSetup(array $settings) {
        // 接管 Tracy Handler
        if ($settings['error_handler']) {
        	set_error_handler($settings['error_handler']);
        }
        if ($settings['exception_handler']) {
    		set_exception_handler($settings['exception_handler']);
        }
    }

    public static function selfSetup(Container $container, string $src) {
        // 初始化配置与语言包
        $container['slion_config'] = function($c) use ($src) {
            return new Utils\Config("$src/config", 'master', 'dev');
        };
        $container['slion_dict'] = function($c) use ($src) {
            return new Utils\Dict("$src/i18n", 'zh_CN.utf8');
        };
    }

    public static function utilsSetup(Container $container, array $setting) {
        foreach ($setting as $name => $config) {
            $container[$name] = function($c) use ($name, $config) {
                $class = "\\Slion\\Utils\\" . ucfirst($name);
                return new $class(...$config);
            };
        }
    }

    public static function iniSetup(array $settings) {
        foreach ($settings as $name => $value) {
            ini_set($name, $value);
        }
    }

    public static function iniCheck(array $settings) {
        foreach ($settings as $name => $value) {
            if (ini_get($name) != $value) {
                throw new \RuntimeException("$name should be $value");
            }
        }
    }

}
