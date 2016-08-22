<?php

namespace Slion\Http\Middleware;

use Slion\Http\Middleware;
use Slion\Utils\IdGenerator;
use Slim\Http\{Request, Response};

/**
 * Description of HeaderX
 *
 * @todo cookie通道兼容
 *
 * @author andares
 */
class RequestId implements Middleware {

    private $name;
    private $secret;
    private $algo;

    public function __construct(string $name = '__request_id',
        string $secret = 'kerafy', string $algo = 'fnv164') {

        $this->name     = $name;
        $this->secret   = $secret;
        $this->algo     = $algo;
    }

    /**
     *
     * @global \Slion\Run $run
     * @param Request $request
     * @param Response $response
     * @param \LionCommon\Http\Middleware\callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response,
        callable $next): Response {

        global $run;
        /* @var $run \Slion\Run */

        $generator = new IdGenerator;
        $generator->algo = $this->algo;
        $id = $generator->prepare(microtime())->hash_hmac($this->secret)
            ->gmp_strval()->get();
        $run->request_id = $id;
        $response = $next($request->withAttribute('request_id', $id), $response);
        return $response;
    }

}
