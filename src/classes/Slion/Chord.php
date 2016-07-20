<?php

namespace Slion;

/**
 * Description of Chord
 *
 * @author andares
 */
class Chord extends Console {
    protected $name = 'chord';

    public function __construct(\Slim\Container $container, string $name = '') {
        parent::__construct($container, $name);
    }
}
