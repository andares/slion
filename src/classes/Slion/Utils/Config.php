<?php
namespace Slion\Utils;

/**
 * Description of Config
 *
 * @author andares
 */
class Config implements \ArrayAccess {
    protected $data     = [];
    private $scenes     = [];
    private $current    = '';

    /**
     *
     * @param string $path
     * @param array $data
     * @return self
     */
    public function inject(string $path, array $data): self {
        $this->data[$path] = $data;
        return $this;
    }

    public function __invoke(string $path) {
        return $this->select($path);
    }

    public function lists(string $path) {
        $list = [];
        foreach ($this->scenes as $scene => $base_dirs) {
            foreach ($base_dirs as $base_dir => $default_scene) {
                $dirs = [
                    "$base_dir/$scene/$path",
                    "$base_dir/$default_scene/$path",
                ];
                foreach ($dirs as $dir) {
                    if (file_exists($dir) && is_dir($dir)) {
                        $it = new \RecursiveDirectoryIterator($dir);
                        foreach ($it as $fileinfo) {
                            /* @var $fileinfo \SplFileInfo */
                            if ($fileinfo->isDir()) {
                                continue;
                            }

                            $list[] = $fileinfo->getBasename('.php');
                        }
                    }
                }
            }
        }
        return $list;
    }

    /**
     *
     * @param type $path
     */
    public function select(string $path) {
        if ($path == $this->current) {
            return $this->current();
        }

        if (!isset($this->data[$path])) {
            $this->data[$path] = [];
            // 现在一次载入所有场景配置
            foreach ($this->scenes as $scene => $base_dirs) {
                foreach ($base_dirs as $base_dir => $default_scene) {
                    $default_scene && $this->load($base_dir, $default_scene, $path);
                    $this->load($base_dir, $scene, $path);
                }
            }
        }
        $this->current = $path;
        return $this->current();
    }

    public function addScene(string $scene, string $base_dir,
        string $default_scene = '') {

        $this->scenes[$scene][$base_dir] = $default_scene;
    }

    private function load($base_dir, $scene, $path) {
        $file = "$base_dir/$scene/$path.php";
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
        return $this->data[$this->current] ?? [];
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
        return $current[$offset] ?? null;
    }

    /**
     * 不维护修改逻辑
     */
    public function offsetSet($offset, $value) {}
    public function offsetUnset($offset) {}

}
