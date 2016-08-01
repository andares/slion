<?php

namespace Slion\Components;

/**
 * Description of Collection
 *
 * @author andares
 */
trait Collection {
    /**
     *
     * @var array
     */
    protected $data = [];

    public function __set($key, $value) {
        return $this->set($key, $value);
    }

    public function __get($key) {
        return $this->get($key);
    }

    public function __isset($key) {
        return $this->has($key);
    }

    public function __unset($key) {
        return $this->remove($key);
    }

    public function push($value, int $mode = 1): self {
        if ($mode === 1) {
            $this->data[] = $value;
        } else {
            array_unshift($this->data, $value);
        }
        return $this;
    }

    public function pop(int $mode = 1) {
        if ($mode === 1) {
            return array_pop($this->data);
        }
        return array_shift($this->data);
    }

    public function set($key, $value): self {
        $this->data[$key] = $value;
        return $this;
    }

    public function fill(array $items): self {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    public function get($key, $default = null) {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    public function all(array $keys = []): array {
        if ($keys) {
            $result = [];
            foreach ($keys as $key) {
                $result[$key] = $this->get($key);
            }
        }
        return $this->data;
    }

    public function keys(): array {
        return array_keys($this->data);
    }

    public function has($key): bool {
        return array_key_exists($key, $this->data);
    }

    public function remove($key): self {
        unset($this->data[$key]);
        return $this;
    }

    public function clear(): self {
        $this->data = [];
        return $this;
    }

    public function count(): int {
        return count($this->data);
    }

}
