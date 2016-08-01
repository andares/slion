<?php

namespace Slion\Meta;

/**
 * Description of Access
 *
 * @author andares
 */
trait Access {

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

}
