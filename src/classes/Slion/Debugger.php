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
        // 记录日志
        $priority   = (($severity & E_NOTICE) || ($severity & E_WARNING)) ? 'warning' : 'error';
        $exc        = new \ErrorException($message, 0, $severity, $file, $line);
        $exc->context = $context;
        dlog($message, $priority, $exc->getTraceAsString());

        return TracyDebugger::errorHandler($severity, $message, $file, $line, $context);
    }

    public static function exceptionHandler(\Exception $exc, bool $exit = true) {
        dlog($exc->getMessage(), 'error', $exc->getTraceAsString());
        return TracyDebugger::exceptionHandler($exc, $exit);
    }

}
