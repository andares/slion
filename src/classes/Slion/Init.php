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

                // TODO 这里加入try，只为处理phpunit中愚蠢的、莫名奇妙地对composer autoload类的载入
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

    public static function debuggerSetup(Collection $settings) {
        $mode    = isset($settings['slion']['tracy']['mode']) ? $settings['slion']['tracy']['mode'] :
            ($settings['displayErrorDetails'] ? Debugger::DEVELOPMENT : Debugger::PRODUCTION);

        // 自建目录
        if (!file_exists($settings['slion']['tracy']['log_dir'])) {
            mkdir($settings['slion']['tracy']['log_dir'], 0777, true);
        }
        Debugger::enable($mode, $settings['slion']['tracy']['log_dir']);

        // 配置
        Debugger::$maxDepth     = $settings['slion']['tracy']['max_depth'];
        Debugger::$maxLength    = $settings['slion']['tracy']['max_length'];
    }

    public static function injectUtils(Container $container) {
        $container['config'] = function ($c) {
            $config = $c->get('settings')['slion']['config'];
            return new Utils\Config($config['base_dir'], $config['scene'], $config['scene_def']);
        };
        $container['dict'] = function ($c) {
            $config = $c->get('settings')['slion']['dict'];
            return new Utils\Dict($config['base_dir'], $config['lang']);
        };
        $container['logger'] = function ($c) {
            $logger = new Utils\Logger();
            return $logger;
        };
    }

    public static function iniSetup() {
        ini_set('assert.exception', 1);

        // 检查一下生产环境配置
        if (Debugger::$productionMode && ini_get('zend.assertions') != -1) {
            throw new \RuntimeException('zend.assertions should be -1 in production mode');
        }
    }

}
