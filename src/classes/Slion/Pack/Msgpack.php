<?php

namespace Slion\Pack;

/**
 * Description of Msgpack
 *
 * @author andares
 */
class Msgpack {
    public function encode($value) {
        return \msgpack_pack($value);
    }

    public function decode($data) {
        return \msgpack_unpack($data);
    }

    public function setSettings(array $settings) {}
}
