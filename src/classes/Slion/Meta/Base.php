<?php
namespace Slion\Meta;

/**
 * Description of Base
 *
 * @author andares
 */
abstract class Base implements \IteratorAggregate {
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
     * 确认数据
     * @throws \UnexpectedValueException
     */
    public function confirm() {
        foreach ($this->getDefault() as $name => $default) {
            if ($this->$name === null && $default === null) {
                $default_method = "_default_$name";
                if (method_exists($this, $default_method)) {
                    $this->$name = $this->$default_method();
                } else {
                    throw new \InvalidArgumentException("meta:" . get_called_class() . " field [$name] could not be empty");
                }
            }

            $method = "_confirm_$name";
            if (method_exists($this, $method)) {
                $this->$name = $this->$method($this->$name);
            }

            if (is_object($this->$name)) {
                $object = $this->$name;
                ($object instanceof self) && $object->confirm();
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
     * @param array|self $data
     * @param array $excludes
     * @return self
     */
    public function fill($data, $excludes = []) {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException("fill data error");
        }

        $fields = $this->getDefault();
        if ($excludes) {
            foreach ($excludes as $name) {
                unset($fields[$name]);
            }
        }
        foreach ($fields as $name => $default) {
            isset($data[$name]) && $this->$name = $data[$name];
        }
        return $this;
    }

    /**
     * 转换到数组
     * @param bool $not_null
     * @return array
     */
    public function toArray(bool $not_null = false): array {
        $arr = [];
        foreach ($this->getDefault() as $name => $default) {
            if (isset($this->$name)) {
                if (is_object($this->$name)) {
                    $object = $this->$name;
                    $value  = method_exists($object, 'toArray') ?
                        $object->toArray() : $object;
                } else {
                    $value  = $this->$name;
                }
            } else {
                $value  = $default;
            }
            if ($value === null && $not_null) {
                continue;
            }
            $arr[$name] = $value;
        }
        return $arr;
    }

    /**
     * 聚合迭代器
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->toArray());
    }

    public function getDefault($key = null) {
        return $key ?
            (array_key_exists($key, static::$_default) ? static::$_default[$key] : null) :
            static::$_default;
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

}
