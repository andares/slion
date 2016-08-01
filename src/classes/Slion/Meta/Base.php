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
            $method = "_confirm_$name";
            if ($this->$name === null && $default === null) {
                throw new \InvalidArgumentException("meta field [$name] could not be empty");
            }
            if (method_exists($this, $method)) {
                $this->$name = $this->$method($this->$name);
            } elseif (is_object($this->$name)) {
                $object = $this->$name;
                if ($object instanceof self) {
                    $object->confirm();
                }
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
     * 转换到数组
     * @return array
     */
    public function toArray() {
        $arr = [];
        foreach ($this->getDefault() as $name => $default) {
            if (isset($this->$name)) {
                if (is_object($this->$name)) {
                    $object     = $this->$name;
                    if ($object instanceof \Slion\Meta\Base) {
                        $arr[$name] = $object->confirm()->toArray();
                    } else {
                        $arr[$name] = method_exists($object, 'toArray') ?
                            $object->toArray() : $object;
                    }
                } else {
                    $arr[$name] = $this->$name;
                }
            } else {
                $arr[$name] = $default;
            }
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

}
