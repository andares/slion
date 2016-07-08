<?php
$container = $app->getContainer();

// 常量
const SLION_ROOT = __DIR__;

// 自动载入
require SLION_ROOT . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR .
    'Slion' . DIRECTORY_SEPARATOR . 'Init.php';
Slion\Init::registerAutoload($container);
Slion\Init::importLibrary(SLION_ROOT . DIRECTORY_SEPARATOR . 'classes');

// 错误处理
Slion\Init::debuggerSetup($container);

// 初始化配置与语言包
Slion\Init::injectUtils($container);

// 载入helper方法
require __DIR__ . DIRECTORY_SEPARATOR . 'helpers.php';
