<?php

namespace Slion\Components;

use Slim\Container;

/**
 * Description of DependenciesTaker
 *
 * @author andares
 */
interface DependenciesTaker {
    public function takeDependencies(Container $container);
}
