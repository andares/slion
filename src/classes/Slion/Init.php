<?php
namespace Slion;

use Slim\Container;
use Slim\Collection;
use Tracy\Debugger;

/**
 * Description of Autoload
 *
 * @author andares
 */
class Init {
    public static function registerAutoload(array $libraries, callable $autoload = null) {
        if ($autoload) {
            spl_autoload_register($autoload);
        } else {
            spl_autoload_register(function ($classname) {
                $classname  = \str_replace("\\", DIRECTORY_SEPARATOR, $classname);

                try {
                    include "$classname.php";
                } catch (\Exception $exc) {
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

    public static function debuggerSetup(array $tracy_settings, Utils\Logger $logger,
        $display_error_details = false) {

        // 设定模式
        if (isset($tracy_settings['is_prod'])) {
            $is_prod = $tracy_settings['is_prod'];
        } else {
            $is_prod = $display_error_details ? Debugger::DEVELOPMENT : Debugger::PRODUCTION;
        }

        // 创建debugger
        Debugger::enable($is_prod, $logger->directory, $logger->email);
        Debugger::setLogger($logger);

        // 配置
        Debugger::$maxDepth     = $tracy_settings['max_depth'];
        Debugger::$maxLength    = $tracy_settings['max_length'];
    }

    public static function utilsSetup($setting) {
        $utils = [];
        foreach ($setting as $name => $config) {
            $class = "\\Slion\\Utils\\" . ucfirst($name);
            $utils[$name] = new $class(...$config);
        }
        \Slion::setUtils($utils);
        return $utils;
    }

    public static function injectUtils(Container $container, array $utils) {
        foreach ($utils as $name => $object) {
            $container[$name] = function($c) use ($object) {
                return $object;
            };
        }
    }

    public static function iniSetup(array $settings) {
        foreach ($settings as $name => $value) {
            ini_set($name, $value);
        }

        // 检查一下生产环境配置
        if (Debugger::$productionMode && ini_get('zend.assertions') != -1) {
            throw new \RuntimeException('zend.assertions should be -1 in production mode');
        }
    }

}
