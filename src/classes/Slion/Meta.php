<?php
namespace Slion;

/**
 *
 * @author andares
 */
abstract class Meta implements \ArrayAccess, \IteratorAggregate, \Serializable, \JsonSerializable {
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
     * 结构及默认值配置
     * @var array
     */
    protected static $_default = [];

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * 构造器
     * @param array $data
     */
    public function __construct(array $data = null) {
        $data && $this->fill($data);
    }

    /**
     * 确认数据
     * @throws \UnexpectedValueException
     */
    public function confirm() {
        foreach ($this->getDefault() as $name => $default) {
            $method = "_confirm_$name";
            if ($this->$name === null && $default === null) {
                throw new \InvalidArgumentException("meta field [$name] could not be empty", 10006);
            }
            if (method_exists($this, $method)) {
                $this->$name = $this->$method($this->$name);
            }
        }

        // 整体confirm勾子
        $this->_confirm();
        return $this;
    }

    /**
     * 待扩展的整体confirm方法
     */
    public function _confirm() {}

    /**
     * 填充数据
     * @param array $data
     * @return self
     */
    public function fill($data) {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException("fill data error");
        }

        foreach ($this->getDefault() as $name => $default) {
            isset($data[$name]) && $this->$name = $data[$name];
        }
        return $this;
    }

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
        return Pack::decode(static::$_serialize_format, $value);
    }

    /**
     * 转换到数组
     * @return array
     */
    public function toArray() {
        $arr = [];
        foreach ($this->getDefault() as $name => $default) {
            $arr[$name] = isset($this->$name) ? $this->$name : $default;
        }
        return $arr;
    }

    /**
     * 重载系列方法
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    public function __get($name) {
        $default = $this->getDefault($name);
        return isset($this->_data[$name]) ? $this->_data[$name] : $default;
    }

    public function __isset($name) {
        return isset($this->_data[$name]);
    }

    public function __unset($name) {
        unset($this->_data[$name]);
    }

    /**
     * Json序列化处理
     * @return array
     */
    public function jsonSerialize() {
        return $this->toArray();
    }

    /**
     * Array Access
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetGet($offset) {
        return $this->$offset;
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetUnset($offset) {
        $this->$offset = null;
    }

    /**
     * 聚合迭代器
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->toArray());
    }

    public function __toString() {
        return json_encode($this);
    }

    public function getDefault($key = null) {
        return $key ?
            (array_key_exists($key, static::$_default) ? static::$_default[$key] : null) :
            static::$_default;
    }

}
