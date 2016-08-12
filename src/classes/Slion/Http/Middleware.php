<?php
namespace Slion\Http;

use Slim\Http\Request as RawRequest;
use Slim\Http\Response as RawResponse;

/**
 * Description of Middleware
 *
 * @author andares
 */
interface Middleware {
    public function __invoke(RawRequest $request, RawResponse $response,
        callable $next): RawResponse;
}
