<?php
/**
 * @author andares
 */
define('IN_TEST', 1);

$GLOBALS['settings'] = require __DIR__ . '/../src/settings.php';
require __DIR__ . '/../vendor/autoload.php';
$run = $GLOBALS['run'];
/* @var $run \Slion\Run */
$run();
//var_dump($run->getBootstrappers());
