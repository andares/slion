<?php
namespace Slion\Utils;

/**
 * Description of Config
 *
 * @author andares
 */
class Config implements \ArrayAccess {
    protected $data = [];
    private $base_dir;
    private $scene;
    private $default_scene;
    private $current_path = '';

    public function __construct($base_dir, $scene, $default_scene = 'default') {
        $this->base_dir         = $base_dir;
        $this->scene            = $scene;
        $this->default_scene    = $default_scene;
    }

    public function __invoke($path) {
        return $this->select($path);
    }

    /**
     *
     * @param type $path
     */
    public function select($path) {
        if ($path == $this->current_path) {
            return $this->current();
        }

        if (!isset($this->data[$path])) {
            $this->data[$path] = [];
            $this->default_scene && $this->load($this->default_scene, $path);
            $this->load($this->scene, $path);
        }
        $this->current_path = $path;
        return $this->current();
    }

    private function load($scene, $path) {
        $file = "$this->base_dir/$scene/$path.php";
        if (file_exists($file)) {
            if (!$this->data[$path]) {
                $this->data[$path] = include $file;
            } else {
                $loaded = include $file;
                foreach ($loaded as $key => $value) {
                    $this->data[$path][$key] = $value;
                }
            }
        }
    }

    /**
     *
     * @return array
     */
    public function current() {
        if (PHP_VERSION_ID >= 70000) {
            return $this->data[$this->current_path] ?? [];
        } else {
            return isset($this->data[$this->current_path]) ?
                $this->data[$this->current_path] : [];
        }
    }

    /**
     * Array Access
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->current()[$offset]);
    }

    public function offsetGet($offset) {
        $current = $this->current();
        if (!isset($current[$offset])) {
            if ($this->default_conf) {
                return $this->default_conf[$offset];
            } else {
                return null;
            }
        }
        return $current[$offset];
    }

    /**
     * 不维护修改逻辑
     */
    public function offsetSet($offset, $value) {}
    public function offsetUnset($offset) {}

}
