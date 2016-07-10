<?php

/**
 * Description of Slion
 *
 * @author andares
 */
class Slion {
    private static $settings = [];
    private static $utils = [];

    final public static function setSettings(array $settings) {
        self::$settings = $settings;
    }

    final public static function getSettings($name) {
        return self::$settings[$name];
    }

    final public static function __callStatic($name, array $arguments) {
        if ($arguments) {
            $method = array_shift($arguments);
            return self::$utils[$name]->$method(...$arguments);
        }
        return self::$utils[$name];
    }

    final public static function setUtils(array $utils) {
        self::$utils = $utils;
    }

    final public static function getUtils($name = null) {
        if (PHP_VERSION_ID >= 70000) {
            return $name ? (self::$utils[$name] ?? null) : self::$utils;
        } else {
            return $name ? (isset(self::$utils[$name]) ? self::$utils[$name] : null) : self::$utils;
        }
    }
}
