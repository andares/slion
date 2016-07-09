<?php
namespace Slion;

/**
 * Description of Test
 *
 * @author andares
 */
class Test {
    public static function init() {
        global $app;
        if ($app instanceof \Slim\App) {
            du('slim app inited', 'test');
        }

        if (\Slion::getUtils()) {
            du('slion inited', 'test');
        }
    }
}
