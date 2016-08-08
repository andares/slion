<?php
// DIC configuration
namespace Slion;

// 配置
$container['config'] = function(\Slim\Container $c) {
    $config = new Utils\Config();
    $config->addScene('master', "$this->root/config", 'dev');
    return $config;
};

// 语言包
$container['dict'] = function(\Slim\Container $c) {
    $dict = new Utils\Dict();
    $dict->addScene('zh_CN.utf8', "$this->root/i18n");
    return $dict;
};

// 日志
$container['logger'] = function(\Slim\Container $c) {
    return new Utils\Logger('/var/tmp');
};

// Hooks
$container['hook'] = function(\Slim\Container $c) {
    return new Hook($c);
};
