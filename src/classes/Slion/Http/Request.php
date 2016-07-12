<?php
namespace Slion\Http;

use Slion\Meta;
use Slion\Pack;
use Slion\Components\DependenciesTaker;


/**
 * Http请求
 *
 * @author andares
 *
 */
abstract class Request extends Meta implements DependenciesTaker {
    protected static $_pack_format = 'json';
    protected static $_packed = [];

    public function confirm() {
        // 解包打包的参数
        foreach (static::$_packed as $name) {
            is_string($this->$name) &&
                $this->$name = Pack::decode(static::$_pack_format, $this->$name);
        }

        parent::confirm();
    }

    public function __debugInfo() {
        return $this->toArray();
    }

    public function takeDependencies(\Slim\Container $container) {}
}
