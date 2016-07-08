<?php

/*
 * license less
 */

namespace Slion;

use Slim\Container;
use Tracy\Debugger;

/**
 * Description of Autoload
 *
 * @author andares
 */
class Init {
    public static function registerAutoload(Container $container, callable $autoload = null) {
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
        foreach ($container->get('settings')['slion']['libraries'] as $dir) {
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

    public static function debuggerSetup(Container $container) {
        $setting = $container->get('settings');
        $mode    = isset($setting['slion']['tracy']['mode']) ? $setting['slion']['tracy']['mode'] :
            ($setting['displayErrorDetails'] ? Debugger::DEVELOPMENT : Debugger::PRODUCTION);

        // 自建目录
        if (!file_exists($setting['slion']['tracy']['log_dir'])) {
            mkdir($setting['slion']['tracy']['log_dir'], 0777, true);
        }
        Debugger::enable($mode, $setting['slion']['tracy']['log_dir']);

        // 配置
        Debugger::$maxDepth     = $setting['slion']['tracy']['max_depth'];
        Debugger::$maxLength    = $setting['slion']['tracy']['max_length'];
    }

    public static function injectUtils(Container $container) {
        $container['config'] = function ($c) {
            $config = $c->get('settings')['slion']['config'];
            return new Utils\Conf($config['base_dir'], $config['scene'], $config['scene_def']);
        };
        $container['dict'] = function ($c) {
            $config = $c->get('settings')['slion']['dict'];
            return new Utils\Dict($config['base_dir'], $config['lang']);
        };

    }

}
