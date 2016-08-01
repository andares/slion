<?php
namespace Slion\Meta;
use Slion\Pack;

/**
 * Description of Base
 *
 * @author andares
 */
trait Serializable {
    /**
     * 序列化打包格式
     * @var string
     */
    protected static $_serialize_format = 'msgpack';

    /**
     * 数组化包版本号
     * @var int
     */
    protected static $_version  = 1;

    /**
     * 根据数字下标的array填充
     * @param array $arr
     * @param boolean $allow_default
     * @return array
     */
    public function fillByArray(array $arr, $allow_default = false) {
        $count  = 0;
        foreach ($this->getDefault() as $name => $default) {
            if ($allow_default && !isset($arr[$count])) {
                $this->$name = $default;
                continue;
            }
            $this->$name = $arr[$count];
            $count++;
        }
    }

    /**
     * 序列化相关
     * @return string
     */
    public function serialize() {
        $arr[] = static::$_version;
        foreach ($this->getDefault() as $name => $default) {
            $arr[] = isset($this->$name) ? $this->$name : $default;
        }
        return static::pack($arr);
    }

    /**
     *
     * @param type $data
     * @throws \UnexpectedValueException
     */
    public function unserialize($data) {
        $arr = static::unpack($data);
        if (!$arr) {
            throw new \UnexpectedValueException("unpack fail");
        }
        $last_version = array_shift($arr);

        // 触发升级勾子
        if ($last_version != static::$_version) {
            $arr = static::_renew($arr, $last_version);
        }
        if (!$arr) {
            throw new \UnexpectedValueException("unserialize fail");
        }

        $this->fillByArray($arr);
    }

    protected static function _renew(array $data, $last_version) {
        return $data;
    }

    protected static function pack(array $value) {
        return Pack::encode(static::$_serialize_format, $value);
    }

    protected static function unpack($data) {
        return Pack::decode(static::$_serialize_format, $data);
    }

    public function toBin() {
        return Pack::encode('msgpack', $this->toArray());
    }


}
