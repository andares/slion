<?php
// 常量
const SLION_ROOT = __DIR__;

// Instantiate the app
$settings   = require APP_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'settings.php';
$app        = new \Slim\App($settings);
$GLOBALS['app'] = $app;

$slion_bootstrap = function($app) {
    $container = $app->getContainer();
    $settings  = $container->get('settings');

    // 自动载入
    require SLION_ROOT . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR .
        'Slion' . DIRECTORY_SEPARATOR . 'Init.php';
    Slion\Init::registerAutoload($settings['slion']['libraries']);
    Slion\Init::importLibrary(SLION_ROOT . DIRECTORY_SEPARATOR . 'classes');

    // 错误处理
    Slion\Init::debuggerSetup($settings);

    // 初始化配置、语言包、日志
    Slion\Init::injectUtils($container);

    // PHP环境
    Slion\Init::iniSetup();

    // 载入helper方法
    require __DIR__ . DIRECTORY_SEPARATOR . 'helpers.php';
};

$slion_bootstrap($app);