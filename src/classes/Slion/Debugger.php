<?php

namespace Slion;
use Tracy\Debugger as TracyDebugger;

/**
 * Description of Debugger
 *
 * @author andares
 */
class Debugger {
    public static function errorHandler(int $severity, string $message, string $file, int $line, $context) {
        $exc        = new \ErrorException($message, 0, $severity, $file, $line);
        $exc->context = $context;
        throw $exc;
    }

    public static function exceptionHandler(\Throwable $throwed, bool $exit = true) {
        if ($throwed instanceof \ErrorException) {
            /* @var $throwed \ErrorException|\Error */
            $severity = $throwed->getSeverity();
            $priority = (($severity & E_NOTICE) || ($severity & E_WARNING)) ? 'warning' : 'error';
            self::log($throwed, $priority);
        } elseif ($throwed instanceof \Error) {
            self::log($throwed, 'error');
        } elseif ($throwed instanceof \Exception) {
            self::log($throwed, 'exception');
        } else {
            self::log($throwed);
        }

        return TracyDebugger::exceptionHandler($throwed, $exit);
    }

    private static function log($message, $priority = 'warning') {
        $logger = TracyDebugger::getLogger();
        $logger($message, $priority);
    }

}
