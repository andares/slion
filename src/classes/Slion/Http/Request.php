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
    protected static $_packed = [];
    protected static $_pack_format      = 'json';
    protected static $_upload_fields    = [];

    public function confirm() {
        // 解包打包的参数
        foreach (static::$_packed as $name) {
            is_string($this->$name) &&
                $this->$name = Pack::decode(static::$_pack_format, $this->$name);
        }

        parent::confirm();
    }

    public function toArray($not_null = false) {
        $arr = parent::toArray($not_null);
        if (static::$_upload_fields) {
            foreach (static::$_upload_fields as $field) {
                unset($arr[$field]);
            }
        }
        return $arr;
    }

    public function __debugInfo() {
        return $this->toArray();
    }

    public function takeDependencies(\Slim\Container $container) {
        // 拉取upload files
        $upload_files = $container->get('dispatcher')->getRawRequest()->getUploadedFiles();
        foreach (static::$_upload_fields as $name) {
            isset($upload_files[$name]) && $this->$name = $upload_files[$name];
        }
    }
}
