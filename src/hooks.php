<?php
namespace Slion;

$app  = $GLOBALS['app'];
$hook = $app->getContainer()->get('hook');
/* @var $hook \Slion\Hook */

// add hook
const HOOK_ERROR_RESPONSE  = 'slion:errof_response';
const HOOK_BEFORE_RESPONSE = 'slion:before_response';

$hook->add(HOOK_BEFORE_RESPONSE, function(array $handlers,
    Http\Dispatcher $caller, \Slim\Container $c) {
    foreach ($handlers as $handler) {
        $handler($caller, $c);
    }
});

$hook->add(HOOK_ERROR_RESPONSE, function(array $handlers,
    Http\ErrorResponse $caller, \Slim\Container $c, \Exception $exc) {
    foreach ($handlers as $handler) {
        $handler($caller, $exc, $c);
    }
});
