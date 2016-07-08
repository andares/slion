<?php

/*
 * license less
 */

namespace Slion\Utils;

use Psr\Log\LoggerInterface;
use Tracy\Debugger;

/**
 * Description of Logger
 *
 * @author andares
 */
class Logger implements LoggerInterface {
    private static $mapping = [
        'debug'     => Debugger::DEBUG,
        'info'      => Debugger::INFO,
        'warning'   => Debugger::WARNING,
        'error'     => Debugger::ERROR,
        'exception' => Debugger::EXCEPTION,
        'critical'  => Debugger::CRITICAL,

        'alert'     => Debugger::WARNING,
        'notice'    => Debugger::WARNING,
        'emergency' => Debugger::CRITICAL,
    ];

    public function __invoke($message, $level = 'debug') {
        if (PHP_VERSION_ID >= 70000) {
            Debugger::log($message, self::$mapping[$level] ?? Debugger::DEBUG);
        } else {
            Debugger::log($message, isset(self::$mapping[$level]) ?
                self::$mapping[$level] : Debugger::DEBUG);
        }
    }

    private function buildContent($message, array $context = []) {
        if (!$context) {
            return $message;
        }

        return '[' . implode('.', $context) . "] $message";
    }

    public function exception($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

   /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = []) {
        Debugger::log($this->buildContent($message, $context), self::$mapping[__FUNCTION__]);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = []) {
        $this($this->buildContent($message, $context), $level);
    }
}
