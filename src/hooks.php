<?php
namespace Slion;

$hook = $container->get('hook');
/* @var $hook \Slion\Hook */

// add hook
const HOOK_BEFORE_ACTION        = 'slion:before_action';
const HOOK_ABORT_CATCHED        = 'slion:abort_catched';
const HOOK_BEFORE_RESPONSE      = 'slion:before_response';
const HOOK_TAKE_ERRORRESPONSE   = 'slion:take_error_response';
const HOOK_REGRESS_RESPONSE     = 'slion:regress_response';

$hook
    ->add(HOOK_BEFORE_ACTION)
    ->add(HOOK_BEFORE_RESPONSE)
    ->add(HOOK_TAKE_ERRORRESPONSE)
    ->add(HOOK_ABORT_CATCHED)
    ->add(HOOK_REGRESS_RESPONSE);


// attach hook taker
$hook->attach(HOOK_REGRESS_RESPONSE, function(Run $run) {

    $slow_time = $run->settings('debug')['slow_log'];
    if ($slow_time) {
        $timecost = (microtime(true) - \Tracy\Debugger::$time) * 1000;
        if ($timecost > $slow_time) {
            $time = str_pad(number_format($timecost, 2, '.', ''), 9, '0', STR_PAD_LEFT);
            dlog("slow logged: $time ms");
        }
    }
});

