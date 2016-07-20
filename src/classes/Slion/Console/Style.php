<?php

namespace Slion\Console;
use Colors\Color;

/**
 * Description of Style
 *
 * @author andares
 */
class Style {
    protected $fixes = [
        'high'  => '>>  %s',
        'warn'  => '??  %s',
        'halt'  => '!!! %s',
    ];

    protected $themes = [
        'info'  => ['cyan'],
        'clear' => ['light_cyan'],
        'high'  => ['dark_gray', 'bg_light_cyan'],
        'warn'  => ['yellow', 'bg_dark_gray'],
        'halt'  => ['black', 'bg_red'],
    ];

    public function __call($name, $arguments) {
        $words = isset($this->fixes[$name]) ?
            sprintf($this->fixes[$name], ...$arguments) : $arguments[0];

        if (isset($this->themes[$name])) {
            $c = new Color($words);
            $c->setUserStyles($this->themes);
            return $c->$name;
        }
        return $words;
    }
}
