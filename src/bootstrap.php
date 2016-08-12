<?php
namespace Slion;
use Slim\{App, Container};
use Tracy\Debugger as TracyDebugger;

// 创建run
$run = new Run();

// setup自身
$run->add('slion', __DIR__)

    ->setup(10, function(string $root) {
        // 约定从全局取配置
        $container  = $GLOBALS['settings'];
        require "$root/dependencies.php";
        $this->ready($container);
    }, 'load dependencies & ready')

    ->setup(20, function(string $root) {
        $settings   = $this->settings()['tracy'];
        $logger     = $this->logger;
        $display_error_details = $this->settings['displayErrorDetails'];

        // 设定模式
        if (isset($settings['is_prod'])) {
            $is_prod = $settings['is_prod'];
        } else { // 当is_prod配置为null或未设置时跟着slim配置走
            $is_prod = $display_error_details ?
                TracyDebugger::DEVELOPMENT : TracyDebugger::PRODUCTION;
        }

        // 创建debugger
        TracyDebugger::enable($is_prod, $logger->directory, $logger->email);
        TracyDebugger::setLogger($logger);

        // 配置
        TracyDebugger::$maxDepth     = $settings['max_depth'];
        TracyDebugger::$maxLength    = $settings['max_length'];
        TracyDebugger::$strictMode   = $settings['strict_mode'];
        TracyDebugger::$scream       = $settings['scream'];
    }, 'setup tracy')

    ->setup(20, function(string $root) {
        $settings   = $this->settings()['debug'];

        // 接管 Tracy Handler
        if ($settings['error_handler']) {
            set_error_handler($settings['error_handler']);
        }
        if ($settings['exception_handler']) {
            set_exception_handler($settings['exception_handler']);
        }

        // debug设置
        Debugger::$debug_in_web = $settings['debug_in_web'];
    }, 'setup debugger & handler')

    ->setup(30, function(string $root) {
        $app        = $this->app();
        $container  = $this->container();
        $settings   = $this->settings();

        require "$root/hooks.php";
        require "$root/middleware.php";
    }, 'init hooks && middlewares')

    ->setup(50, function(string $root) {
        require "$root/helpers.php";
    }, 'load helpers');

