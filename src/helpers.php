<?php
use Tracy\Debugger;

if (!function_exists('trans')) {
    function trans($path, $key, $values = []) {
        $dict = \Slion::dict();
        /* @var $dict Slion\Utils\Dict */
        $dict($path);

        if ($values) {
            return $dict->assign($key, $values);
        }
        return $dict[$key];
    }
}

if (!function_exists('conf')) {
    function conf($path, $key = null) {
        $config = \Slion::config();
        /* @var $config Slion\Utils\Config */
        if ($key) {
            $config($path);
            return $config[$key];
        }
        return $config($path);
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

        if (PHP_VERSION_ID >= 70000) {
            Debugger::barDump($var, $flag ?? "Dump #$count");
        } else {
            Debugger::barDump($var, $flag ? $flag : "Dump #$count");
        }
    }
}

if (!function_exists('dd')) {
    function dd($var, $flag = null) {
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
