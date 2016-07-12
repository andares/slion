<?php
// create slim app
$settings   = $GLOBALS['settings'];
$app        = new \Slim\App($settings);
$GLOBALS['app'] = $app;

$slion_bootstrap = function(\Slim\App $app, array $settings) {
    // 自动载入
    require __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR .
        'Slion' . DIRECTORY_SEPARATOR . 'Init.php';
    Slion\Init::registerAutoload($settings['libraries']);
    Slion\Init::importLibrary(__DIR__ . DIRECTORY_SEPARATOR . 'classes');

    // 初始化配置、语言包、日志等并注入容器
    Slion\Init::utilsSetup($app->getContainer(), $settings['utils']);

    // Pack 配置
    \Slion\Pack::setSettings($settings['pack']);

    // 调试功能
    $display_error_details = $app->getContainer()->get('settings')['displayErrorDetails'];
    Slion\Init::debuggerSetup($settings['tracy'], $app->getContainer()->get('logger'),
        $display_error_details);

    // PHP环境
    Slion\Init::iniSetup($settings['php_ini']);

    // 载入helper方法
    foreach ($settings['helpers'] as $helpers_file) {
        require $helpers_file;
    }
    require __DIR__ . DIRECTORY_SEPARATOR . 'helpers.php';

    // Hooks
    $app->getContainer()['hook'] = function(\Slim\Container $c) {
        return new \Slion\Hook($c);
    };
    require __DIR__ . DIRECTORY_SEPARATOR . 'hooks.php';
};

$slion_bootstrap($app, $settings['slion_settings']);