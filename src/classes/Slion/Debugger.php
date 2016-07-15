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

    public static function exceptionHandler(\Exception $exc, bool $exit = true) {
        if ($exc instanceof \ErrorException) {
            /* @var $exc \ErrorException */
            $severity = $exc->getSeverity();
            $priority = (($severity & E_NOTICE) || ($severity & E_WARNING)) ? 'warning' : 'error';
            dlog($exc->getMessage(), $priority, $exc->getTraceAsString());
        } else {
            dlog($exc->getMessage(), 'error', $exc->getTraceAsString());
        }
        return TracyDebugger::exceptionHandler($exc, $exit);
    }

}
