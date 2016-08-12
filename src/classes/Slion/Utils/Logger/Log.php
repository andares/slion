<?php
namespace Slion\Utils\Logger;
use Slion\Pack;

/**
 * Description of Log
 *
 * 日志改进，内容：
ip 版本 渠道号 用户id 接口 参数 返回错误码 时间 耗时
 *
 * @author andares
 */
class Log {
    protected $id = '';
    protected $name;
    protected $lines = [];
    protected $extra = [];

    public function __construct(string $name) {
        $this->name     = $name;
    }

    public function setId(string $id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function __set(string $name, $value) {
        $this->lines[$name] = $value;
    }

    public function __get(string $name) {
        return $this->line[$name];
    }

    public function setExtra(array $extra_info) {
        $this->extra = array_merge($this->extra, $extra_info);
    }

    public function getDate() {
        return date($this->date_formate);
    }

    protected function makeOutput(string $date) {
        global $run;
        /* @var $run \Slion\Run */
        $id = $this->id ? $this->id : $run->getId();

        $output = [
            '_' => [
                'id'    => $id,
                'date'  => $date,
                'name'  => $this->catalog,
            ],
            '#' => $this->lines,
            '@' => $this->extra,
        ];
        return $output;
    }

    public function __toString() {
        return Pack::encode('json', $this->makeOutput());
    }
}
