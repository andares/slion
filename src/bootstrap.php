<?php
// create slim app
$settings   = $GLOBALS['settings'];
$app        = new Slim\App($settings);
$GLOBALS['app'] = $app;

$slion_bootstrap = function(Slim\Container $container, array $settings) {
    global $chord_bootstrap;

    // 自动载入
    require __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR .
        'Slion' . DIRECTORY_SEPARATOR . 'Init.php';
    Slion\Init::registerAutoload($settings['libraries']);
    Slion\Init::importLibrary(__DIR__ . DIRECTORY_SEPARATOR . 'classes');

    // 初始化自身相关资源
    Slion\Init::selfSetup($container, __DIR__);

    // 初始化配置、语言包、日志等并注入容器
    Slion\Init::utilsSetup($container, $settings['utils']);

    // Pack 配置
    Slion\Pack::setSettings($settings['pack']);

    // tracy 初始化
    $display_error_details = $container->get('settings')['displayErrorDetails'];
    Slion\Init::tracySetup($settings['tracy'], $container->get('logger'),
        $display_error_details);

    // 接管tracy handler
    Slion\Init::debuggerSetup($settings['debug']);

    // 载入helper方法
    foreach ($settings['helpers'] as $helpers_file) {
        require $helpers_file;
    }
    require __DIR__ . DIRECTORY_SEPARATOR . 'helpers.php';

    // Hooks
    $container['hook'] = function(\Slim\Container $c) {
        return new \Slion\Hook($c);
    };
    require __DIR__ . DIRECTORY_SEPARATOR . 'hooks.php';

    // PHP环境
    Slion\Init::iniSetup($settings['php_ini']);

    // 检查PHP assert的配置
    if (is_prod()) {
        Slion\Init::iniCheck($settings['php_ini_check']);
    }

    // chord boot
    $chord_bootstrap && $chord_bootstrap($container, __DIR__);
};

$slion_bootstrap($app->getContainer(), $settings['slion_settings']);