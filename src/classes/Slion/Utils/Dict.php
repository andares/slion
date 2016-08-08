<?php

namespace Slion\Utils;

/**
 * Description of Dict
 *
 * @author andares
 */
class Dict extends Config {
    public function assign($key, ...$values) {
        return sprintf($this[$key], ...$values);
    }

    /**
     * 改变了在找不到的情况下的输出格式
     * @param type $offset
     * @return string
     */
    public function offsetGet($offset) {
        $result = parent::offsetGet($offset);
        return $result ? $result : $offset;
    }

}
