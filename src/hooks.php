<?php
namespace Slion;

const HOOK_BEFORE_RESPONSE = '_before_response';

$app  = $GLOBALS['app'];
$hook = $app->getContainer()->get('hook');
/* @var $hook \Slion\Hook */
$hook->add(HOOK_BEFORE_RESPONSE, function(array $handlers,
    Http\Dispatcher $caller, \Slim\Container $c) {
    foreach ($handlers as $handler) {
        $handler($caller, $c);
    }
});
