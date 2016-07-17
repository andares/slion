<?php

namespace Slion\Utils;

/**
 * Description of Dict
 *
 * @author andares
 */
class Dict extends Config {
    public function __construct($base_dir, $scene, $default_scene = null) {
        parent::__construct($base_dir, $scene, $default_scene);
    }

    public function assign($key, array $values) {
        return sprintf($this[$key], ...$values);
    }

    public function offsetGet($offset) {
        $result = parent::offsetGet($offset);
        return $result ? $result : $offset;
    }

}
