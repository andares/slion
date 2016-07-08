<?php
use Tracy\Debugger;

if (!function_exists('trans')) {
    function trans($path, $key, $values = []) {
        global $app;
        $dict = $app->getContainer()->dict;
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
        global $app;
        $config = $app->getContainer()->config;
        /* @var $config Slion\Utils\Conf */
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
            Debugger::dump("=== Dump $title ===");
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

if (!function_exists('dl')) {
    function dlog($message, $level = 'info') {
        static $mapping = [
            'debug' => Debugger::DEBUG,
            'info' => Debugger::INFO,
            'warning' => Debugger::WARNING,
            'error' => Debugger::ERROR,
            'exception' => Debugger::EXCEPTION,
            'critical' => Debugger::CRITICAL,
        ];
        !isset($mapping[$level]) && $level = 'debug';
        Debugger::log($message, $mapping[$level]);
    }
}

if (!function_exists('timer')) {
    function timer($name = 0) {
        static $names = [];
        if (!isset($names[$name])) {
            // 开始
        }
    }
}
