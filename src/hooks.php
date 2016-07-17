<?php
namespace Slion;

$app  = $GLOBALS['app'];
$hook = $app->getContainer()->get('hook');
/* @var $hook \Slion\Hook */

// add hook
const HOOK_ERROR_RESPONSE   = 'slion:error_response';
const HOOK_BEFORE_RESPONSE  = 'slion:before_response';
const HOOK_REGRESS_RESPONSE = 'slion:regress_response';

$hook->add(HOOK_BEFORE_RESPONSE, function(array $handlers,
    Http\Dispatcher $caller, \Slim\Container $c, Http\Response $response) {
    foreach ($handlers as $handler) {
        $handler($caller, $response, $c);
    }
});

$hook->add(HOOK_ERROR_RESPONSE, function(array $handlers,
    Http\ErrorResponse $caller, \Slim\Container $c, \Throwable $exc) {
    foreach ($handlers as $handler) {
        $handler($caller, $exc, $c);
    }
});

$hook->add(HOOK_REGRESS_RESPONSE, function(array $handlers,
    Http\Dispatcher $caller, \Slim\Container $c, Http\Response $response) {
    foreach ($handlers as $handler) {
        $handler($caller, $response, $c);
    }
});

// attach hook taker
$hook->attach(HOOK_REGRESS_RESPONSE, function(Http\Dispatcher $caller,
    Http\Response $response, \Slim\Container $c) {

    $slow_time = $c->get('slion_settings')['debug']['slow_log'];
    if ($slow_time) {
        $timecost = (microtime(true) - \Tracy\Debugger::$time) * 1000;
        if ($timecost > $slow_time) {
            $time = str_pad(number_format($timecost, 2, '.', ''), 9, '0', STR_PAD_LEFT);
            dlog("slow logged: $time ms");
        }
    }
});

