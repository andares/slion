<?php
namespace Slion\Http;

use Slim\Http\{
    Response as RawResponse,
    Request as Raw
};
use Slion\Meta;
use Slion\Pack;
use Slion\Utils\Logger\Log;


/**
 * Http请求
 *
 * @author andares
 *
 */
class Request extends Meta\Base
    implements \ArrayAccess, \Serializable, \JsonSerializable {
    use Meta\Access, Meta\Serializable, Meta\Json;

    protected static $_packed = [];
    protected static $_pack_format      = 'json';
    protected static $_upload_fields    = [];

    /**
     *
     * @var Raw
     */
    protected $_raw;

    /**
     *
     * @var RawResponse
     */
    protected $_response = null;

    /**
     *
     * @param Raw $raw
     * @param RawResponse $response
     */
    public function __construct(Raw $raw, RawResponse $response = null) {
        $this->_raw         = $raw;
        $this->_response    = $response;

        $this->fill($raw->getParams());
        $this->takeUploadFiles();
    }

    /**
     *
     * @return Raw
     */
    public function raw(): Raw {
        return $this->_raw;
    }

    /**
     *
     * @return self
     */
    public function confirm(): self {
        // 解包打包的参数
        foreach (static::$_packed as $name) {
            is_string($this->$name) &&
                $this->$name = Pack::decode(static::$_pack_format, $this->$name);
        }

        return parent::confirm();
    }

    /**
     *
     * @param bool $not_null
     * @return array
     */
    public function toArray(bool $not_null = false): array {
        $arr = parent::toArray($not_null);

        // 移除upload类型字段
        if (static::$_upload_fields) {
            foreach (static::$_upload_fields as $field) {
                unset($arr[$field]);
            }
        }
        return $arr;
    }

    /**
     *
     * @return array
     */
    public function __debugInfo(): array {
        return $this->toArray();
    }

    /**
     *
     */
    protected function takeUploadFiles() {
        $upload_files = $this->_raw->getUploadedFiles();
        foreach (static::$_upload_fields as $name) {
            isset($upload_files[$name]) && $this->$name = $upload_files[$name];
        }
    }

    /**
     *
     * @return Log
     */
    public function toLog(): Log {
        $log = new Log('receive request');
        $log->data = $this->toArray();
        return $log;
    }
}
