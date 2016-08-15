<?php

namespace Slion;
use Tracy\Debugger as TracyDebugger;

/**
 * Description of Debugger
 *
 * @author andares
 */
class Debugger {
    public static $debug_in_web = false;

    /**
     *
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @param string $context
     * @throws \ErrorException
     */
    public static function errorHandler(int $severity, string $message,
        string $file, int $line, $context) {

        $exc        = new \ErrorException($message, 0, $severity, $file, $line);
        $exc->context = $context;
        throw $exc;
    }

    /**
     *
     * @param \Error $throwed
     * @param bool $exit
     * @return string|null exception file path
     */
    public static function exceptionHandler(\Throwable $throwed,
        bool $exit = true) {

        if ($throwed instanceof \ErrorException) {
            /* @var $throwed \ErrorException|\Error */
            $severity = $throwed->getSeverity();
            $priority = (($severity & E_NOTICE) || ($severity & E_WARNING)) ?
                'warning' : 'error';
        } elseif ($throwed instanceof \Error) {
            $priority = 'error';
        } elseif ($throwed instanceof \Exception) {
            $priority = 'exception';
        } else {
            $priority = 'warning';
        }
        $file = self::log($throwed, $priority);

        // 是否使用tracy debug handler
        if (is_prod() || $throwed->getCode() || !self::$debug_in_web) {
            return $file;
        }
        TracyDebugger::exceptionHandler($throwed, $exit);
    }

    /**
     *
     * @param mixed $message
     * @param string $priority
     * @return string|null exception file path
     */
    private static function log($message, string $priority = 'warning') {
        $logger = TracyDebugger::getLogger();
        return $logger($message, $priority);
    }

}
