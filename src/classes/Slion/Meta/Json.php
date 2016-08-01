<?php

namespace Slion\Meta;
use Slion\Pack;

/**
 * Description of jsonSerialize
 *
 * @author andares
 */
trait Json {

    public function __toString() {
        return Pack::encode('json', $this->toArray());
    }

    /**
     * Json序列化处理
     * @return array
     */
    public function jsonSerialize() {
        return $this->toArray();
    }
}
