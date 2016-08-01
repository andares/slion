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
        $handler($caller, $c, $response);
    }
});

$hook->add(HOOK_BEFORE_ACTION, function(array $handlers,
    Http\Controller $caller, \Slim\Container $c, string $action,
    Http\Response $response, $request = null) {
    foreach ($handlers as $handler) {
        $handler($caller, $c, $action, $response, $request);
    }
});

$hook->add(HOOK_ERROR_RESPONSE, function(array $handlers,
    Http\ErrorResponse $caller, \Slim\Container $c, \Throwable $exc) {
    foreach ($handlers as $handler) {
        $handler($caller, $c, $exc);
    }
});

$hook->add(HOOK_REGRESS_RESPONSE, function(array $handlers,
    Http\Dispatcher $caller, \Slim\Container $c, Http\Response $response) {
    foreach ($handlers as $handler) {
        $handler($caller, $c, $response);
    }
});

// attach hook taker
$hook->attach(HOOK_REGRESS_RESPONSE, function(Http\Dispatcher $caller,
    \Slim\Container $c, Http\Response $response) {

    $slow_time = $c->get('slion_settings')['debug']['slow_log'];
    if ($slow_time) {
        $timecost = (microtime(true) - \Tracy\Debugger::$time) * 1000;
        if ($timecost > $slow_time) {
            $time = str_pad(number_format($timecost, 2, '.', ''), 9, '0', STR_PAD_LEFT);
            dlog("slow logged: $time ms");
        }
    }
});

