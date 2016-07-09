<?php

namespace Slion\Http;

use Slim\Container;

/**
 * Description of DependenciesTaker
 *
 * @author andares
 */
interface DependenciesTaker {
    public function takeDependencies(Container $container);
}
