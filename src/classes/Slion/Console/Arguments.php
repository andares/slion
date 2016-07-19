<?php
namespace Slion\Console;

/**
 * 命令行参数解析器
 *
 * @todo 此版本中参数之间不联动，所以不存在可选参数组的概念
 *
 * chord namespace:command <action> <target> <-t:1>
 * 单个按位置识别参数 <action> <target>
 * 默认值 <quiet:0>
 * 取3个参数 <more:.3>
 * 多个参数 <more:.*>
 * 限制选项 <mode:ASC|DESC>
 * 无序参数 <-t> <--host>
 * 别名 <-m $mode:ASC|DESC>
 * 多参数名 <-f|--force $force:1|0>
 * 可选参数 [-s]
 * 开关参数 <-y=1> <-y $yes=1|0>
 *
 * 规则如下：
 * 单横线后面跟单个字符，一律为开关参数，存在则返回1，如：
 *  -s -m 则返回s=1, m=1
 *
 * 单横线后面跟多个字符，表示可赋值参数，参数与命名间以空格分隔，如：
 *  -mode http -host 127.0.0.1 则返回mode='http', host='127.0.0.1'
 *
 * 双横线表示之后参数名与值以等号=分隔，如果不带等号则返回1，如：
 *  --mode=http --retry 则返回mode='http', retry=1
 *
 * @author andares
 */
class Arguments {
    /**
     *
     * @var array
     */
    private $pattern;

    public function __construct(string $pattern) {
        $this->pattern = $this->parsePattern($pattern);
    }

    private function parsePattern(string $pattern): array {
        $pattern = '<action> <target> <-t:1> [-S] <-m $mode:ASC|DESC> <-y $yes=1|0> <-f|--force $force:1|0>';

        $arg    = '([^\>\] \:\=]*)';
        $name   = '( \$([^\>\] \:\=]*))';
        $value  = '((\:|\=)([^\>\]]*))';
        preg_match_all("/(\<|\[)$arg$name?$value?(\>|\])/", $pattern, $result);
        foreach ($result[0] as $key => $body) {
            // 处理名字

            $option['require'] = $result[1][$key] === '<';
            $option['require'] = $result[1][$key] === '<';
        }
        du($result);
        return [];
    }

    public function __invoke(array $argv): self {
        return $this;
    }
}

