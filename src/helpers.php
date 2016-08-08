<?php
use Tracy\Debugger;
use Tracy\Dumper;

if (!function_exists('abort')) {
    function abort(\Throwable $e, callable $maker = null, ...$arguments): Slion\Abort {
        $abort = new Slion\Abort($e);
        return $maker ? $maker($abort, ...$arguments) : $abort;
    }
}

if (!function_exists('path_for')) {
    function path_for(string $module, string $controller, string $action, array $queries = [], $ext = '') {
        global $app;
        return $app->getContainer()->get('router')->pathFor($module, [
            'controller'    => $controller,
            'action'        => $action,
            'ext'           => $ext
        ], $queries);
    }
}

/**
 * common func, can be covered
 */
if (!function_exists('tr')) {
    function tr($path, $key, ...$values) {
        global $app;
        /* @var $app \Slim\App */
        $dict = $app->getContainer()->get('dict');
        /* @var $dict Slion\Utils\Dict */
        $dict($path);

        if ($values) {
            return $dict->assign($key, ...$values);
        }
        return $dict[$key];
    }
}

if (!function_exists('cf')) {
    function cf($path, $key = null) {
        global $app;
        /* @var $app \Slim\App */
        $config = $app->getContainer()->get('config');
        /* @var $config Slion\Utils\Config */
        if ($key) {
            $config($path);
            return $config[$key];
        }
        return $config($path);
    }
}

if (!function_exists('dt')) {
    function dt($var, $flag = null) {
        static $count = 0;
        $count++;

        if ($flag) {
            echo Dumper::toText("=== Dump $flag ===");
        } else {
            echo Dumper::toText("=== Dump #$count ===");
        }
        echo Dumper::toText($var);
    }
}

if (!function_exists('du')) {
    function du($var, $flag = null) {
        static $count = 0;
        $count++;

        if ($flag) {
            Debugger::dump("=== Dump $flag ===");
        } else {
            Debugger::dump("=== Dump #$count ===");
        }
        Debugger::dump($var);
    }
}

if (!function_exists('dbar')) {
    function dbar($var, $flag = null) {
        static $count = 0;
        $count++;

        Debugger::barDump($var, $flag ?? "Dump #$count");
    }
}

if (!function_exists('ds')) {
    function ds($var, $flag = null) {
        du($var, $flag);
        die();
    }
}

if (!function_exists('dlog')) {
    function dlog($message, $priority = 'debug') {
        $logger = Debugger::getLogger();
        $logger($message, $priority);
    }
}

if (!function_exists('is_prod')) {
    function is_prod() {
        return Debugger::$productionMode;
    }
}

if (!function_exists('timer')) {
    function timer($name = null) {
        return Debugger::timer($name);
    }
}
