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
    protected $body = [];
    protected $extra = [];
    protected $date  = '';

    public function __construct(string $name) {
        $this->name     = $name;
    }

    public function setId(string $id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setExcFile(string $file): self {
        $this->extra['exc_file'] = $file;
        return $this;
    }

    public function __set(string $name, $value) {
        $this->body[$name] = $value;
    }

    public function __get(string $name) {
        return $this->body[$name];
    }

    public function setExtra(array $extra_info): self {
        $this->extra = array_merge($this->extra, $extra_info);
        return $this;
    }

    public function setDate(string $date): self {
        $this->date = $date;
        return $this;
    }

    public function setBody(array $body): self {
        $this->body[] = $body;
        return $this;
    }

    public function makeOutput() {
        global $run;
        /* @var $run \Slion\Run */
        $id = $this->id ? $this->id : $run->getId();
        $this->date && $base['date'] = $this->date;
        $base['id']   = $id;
        $base['name'] = $this->name;

        $output = [
            'base'  => $base,
            'body'  => $this->body,
            'extra' => $this->extra,
        ];
        return $output;
    }

    public function __toString() {
        return Pack::encode('json', $this->makeOutput());
    }
}
