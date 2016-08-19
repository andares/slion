<?php

namespace Slion\Utils;

/**
 * Description of IdGenerator
 *
 * @author andares
 */
class IdGenerator {
    /**
     * use hash_algos() get list.
     *
     * @var string
     */
    public $algo = 'ripemd320';

    private $raw;
    private $data;

    public function prepare(string $data): self {
        $this->data     = $this->raw = $data;
        return $this;
    }

    public function get() {
        return $this->data;
    }

    public function hash_hmac(string $secret = null, $return_raw = false): self {
        $this->data = hash_hmac($this->algo, $this->data, $secret, $return_raw);
        return $this;
    }

    public function gmp_strval($bit = 62): self {
        $data = "0x$this->data";
        $gmp  = gmp_init($data);
        $this->data = gmp_strval($gmp, $bit);
        return $this;
    }

    public function base64(): self {
        $this->data = base64_encode($this->data);
        return $this;
    }

    public function urlencode(): self {
        $this->data = urlencode($this->data);
        return $this;
    }

    public function pack(string $pattern): self {
        $this->data = pack($pattern, $this->data);
        return $this;
    }

    public function strtoupper(): self {
        $this->data = strtoupper($this->data);
        return $this;
    }

    public function strtolower(): self {
        $this->data = strtolower($this->data);
        return $this;
    }

    public function base_convert(int $from, int $to): self {
        $this->data = base_convert($this->data, $from, $to);
        return $this;
    }

    public function uuid($trim = true) {
        $id = uuid_create();
        $this->data = $trim ? str_replace('-', '', $id) : $id;
        return $this;
    }
}
