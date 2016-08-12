<?php
namespace Slion;

/**
 * Description of Test
 *
 * @author andares
 */
class Test {
    public static function run(callable $func, $times = 1) {
        timer();
        for ($i = 0; $i < $times; $i++) {
            $result = $func();
        }
        $cost = timer();
        if ($times > 1) {
            du($cost, 'time cost');
        }
        return $result;
    }
}
