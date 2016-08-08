<?php
namespace Slion;

use Slim\Container;
use Tracy\Debugger;

/**
 * Description of Autoload
 *
 * @author andares
 */
abstract class Init {
    /**
     *
     * @var []
     */
    protected static $imported = [];

    /**
     *
     * @var string
     */
    protected $root;

    public function __construct(string $root) {
        $this->root = $root;
    }

    public function getRoot() {
        return $this->root;
    }

    public function head(\Slim\App $app, \Slim\Container $container, array $settings) {}
    public function tail(\Slim\App $app, \Slim\Container $container, array $settings) {}

    protected function phpIniReady(array $setup_list = [], array $check_list = []) {
        foreach ($setup_list as $name => $value) {
            ini_set($name, $value);
        }
        foreach ($check_list as $name => $value) {
            if (ini_get($name) != $value) {
                throw new \RuntimeException("PHP ini [$name] should be $value");
            }
        }
    }

    protected function importLibrary(string $dir): array {
        if (!isset(self::$imported[$dir])) {
            ini_set('include_path', $dir . PATH_SEPARATOR . ini_get('include_path'));
            self::$imported[$dir] = 1;
        }
        return self::$imported;
    }

}
