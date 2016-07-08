<?php
namespace Slion\Http;

use Slion\Utils\Meta;

/**
 * Http请求 dev_id  acc_id nonce token
 *
 * @author andares
 */
abstract class Request extends Meta {
    public $dev_id  = '';
    public $acc_id  = '';
    public $nonce   = '';
    public $token   = '';

    private static $header_mapping = [
        'dev_id'    => 'DEV_ID',
        'acc_id'    => 'ACC_ID',
        'nonce'     => 'NONCE',
        'token'     => 'TOKEN',
    ];

    private static $cookie_mapping = [
        'dev_id'    => 'dev_id',
        'acc_id'    => 'acc_id',
        'nonce'     => 'nonce',
        'token'     => 'token',
    ];

    public function __construct(array $data = null) {
        parent::__construct($data);
        $this->loadHeader($data);
    }

    protected function loadHeader(array $request) {
        foreach (self::$header_mapping as $name => $key) {
            if (isset($_SERVER["HTTP_$key"])) {
                $this->$name = $_SERVER["HTTP_$key"];
            } elseif (isset($_COOKIE[self::$cookie_mapping[$name]])) {
                $this->$name = $_COOKIE[self::$cookie_mapping[$name]];
            } elseif (isset($request[$name])) {
                $this->$name = $request[$name];
            }
        }
    }

    public function jsonSerialize() {
        $arr = $this->toArray();
        $arr['__HEADER__'] = [];
        foreach (self::$header_mapping as $name => $key) {
            $arr['__HEADER__'][$name] = $this->$name;
        }
        return $arr;
    }
}
