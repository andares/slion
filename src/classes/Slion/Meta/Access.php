<?php

namespace Slion\Meta;

/**
 * Description of Access
 *
 * @author andares
 */
trait Access {

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
