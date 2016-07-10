<?php
// create slim app
$settings   = $GLOBALS['settings'];
if (isset($settings['settings'])) {
    $app        = new \Slim\App($settings);
    $GLOBALS['app'] = $app;
}

$slion_bootstrap = function(array $settings) {
    global $app;

    // 自动载入
    require __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR .
        'Slion' . DIRECTORY_SEPARATOR . 'Init.php';
    Slion\Init::registerAutoload($settings['libraries']);
    Slion\Init::importLibrary(__DIR__ . DIRECTORY_SEPARATOR . 'classes');

    // 存一下settings
    \Slion::setSettings($settings);

    // 初始化配置、语言包、日志等
    $utils = Slion\Init::utilsSetup($settings['utils']);

    // 调试功能
    if ($app) {
        $display_error_details = $app->getContainer()->get('settings')['displayErrorDetails'];
    } else {
        $display_error_details = false;
    }
    Slion\Init::debuggerSetup($settings['tracy'], $utils['logger'], $display_error_details);

    // PHP环境
    Slion\Init::iniSetup($settings['php_ini']);

    // 载入helper方法
    foreach ($settings['helpers'] as $helpers_file) {
        require $helpers_file;
    }
    require __DIR__ . DIRECTORY_SEPARATOR . 'helpers.php';

    // 注入容器
    if ($app) {
        Slion\Init::injectUtils($app->getContainer(), $utils);
    }
};

$slion_bootstrap($settings['slion_settings']);