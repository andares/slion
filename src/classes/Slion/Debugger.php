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
            dlog($throwed->getMessage(), $priority, $throwed->getTraceAsString());
        } elseif ($throwed instanceof \Error) {
            dlog(self::makeErrorLine($throwed), 'error', $throwed->getTraceAsString());
        } elseif ($throwed instanceof \Exception) {
            dlog(self::makeExceptionLine($throwed), 'exception', $throwed->getTraceAsString());
        } else {
            dlog($throwed->getMessage(), 'warning', $throwed->getTraceAsString());
        }

        return TracyDebugger::exceptionHandler($throwed, $exit);
    }

    protected static function makeErrorLine(\Error $error) {
        return '[' . get_class($error) . '](' . $error->getCode() . ') ' . $error->getMessage();
    }

    protected static function makeExceptionLine(\Exception $exc) {
        return '[' . get_class($exc) . '](' . $exc->getCode() . ') ' . $exc->getMessage();
    }

}
