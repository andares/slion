<?php
namespace Slion;
use Slim\{App, Container};
use Tracy\Debugger;

// 创建run
$run = new Run();

// setup自身
$run->add('slion', __DIR__)

    ->setup(10, function(string $root, Run $run) {
        // 约定从全局取配置
        $container  = $GLOBALS['settings'];
        require "$root/dependencies.php";
        $run->ready($container);
    }, 'load dependencies & ready')

    ->setup(20, new class() {
        public function __invoke(string $root, Run $run) {
            $app        = $run->app();
            $container  = $run->container();
            $settings   = $run->settings();

            $this->setTracy($settings['tracy'], $container->get('logger'),
                $container->get('settings')['displayErrorDetails']);
            $this->setErrorHandler($settings['debug']);
        }

        private function setTracy(array $settings, Utils\Logger $logger,
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

        private function setErrorHandler(array $settings) {
            // 接管 Tracy Handler
            if ($settings['error_handler']) {
                set_error_handler($settings['error_handler']);
            }
            if ($settings['exception_handler']) {
                set_exception_handler($settings['exception_handler']);
            }
        }
    }, 'init tracy & debugger')

    ->setup(30, function(string $root, Run $run) {
        $app        = $run->app();
        $container  = $run->container();
        $settings   = $run->settings();

        require "$root/hooks.php";
    }, 'init hooks')

    ->setup(50, function(string $root, Run $run) {
        $app        = $run->app();
        $container  = $run->container();
        $settings   = $run->settings();

        require "$root/helpers.php";
    }, 'load helpers');

