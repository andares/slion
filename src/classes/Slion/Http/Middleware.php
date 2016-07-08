<?php

/*
 * license less
 */

namespace Slion\Http;

use Slim\Http\Request as SlimRequest;
use Slim\Http\Response as SlimResponse;

/**
 * Description of Middleware
 *
 * @author andares
 */
interface Middleware {
    public function __invoke(SlimRequest $request, SlimResponse $response, callable $next);
}
