<?php
namespace Slion;
use Tracy\Debugger;

// 约定从全局取配置
$settings   = $GLOBALS['settings'];

// 创建run
$run = new Run(new \Slim\App($GLOBALS['settings']));

// 准备基础环境
$run->prepare();

// setup自身
$run->setup('slion', new class(__DIR__) extends Init {
    public function head(\Slim\App $app, \Slim\Container $container, array $settings) {
        require "$this->root/dependencies.php";

        $this->setTracy($settings['tracy'], $container->get('logger'),
            $container->get('settings')['displayErrorDetails']);
        $this->setErrorHandler($settings['debug']);

        require "$this->root/hooks.php";
    }

    public function tail(\Slim\App $app, \Slim\Container $container, array $settings) {
        require "$this->root/helpers.php";
    }

    protected function setTracy(array $settings, Utils\Logger $logger,
        bool $display_error_details) {

        // 设定模式
        if (isset($settings['is_prod'])) {
            $is_prod = $settings['is_prod'];
        } else { // 当is_prod配置为null或未设置时跟着slim配置走
            $is_prod = $display_error_details ? Debugger::DEVELOPMENT : Debugger::PRODUCTION;
        }

        // 创建debugger
        Debugger::enable($is_prod, $logger->directory, $logger->email);
        Debugger::setLogger($logger);

        // 配置
        Debugger::$maxDepth     = $settings['max_depth'];
        Debugger::$maxLength    = $settings['max_length'];
        Debugger::$strictMode   = $settings['strict_mode'];
        Debugger::$scream       = $settings['scream'];
    }

    public function setErrorHandler(array $settings) {
        // 接管 Tracy Handler
        if ($settings['error_handler']) {
        	set_error_handler($settings['error_handler']);
        }
        if ($settings['exception_handler']) {
    		set_exception_handler($settings['exception_handler']);
        }
    }
});

