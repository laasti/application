<?php

namespace Laasti\Application\Http;

use InvalidArgumentException;
use Laasti\Application\Middlewares\IncompleteRunException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpRunner
{
    protected $middlewares;

    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;

        if (!count($this->middlewares)) {
            throw new InvalidArgumentException('You must at least define 1 middleware to use with HttpRunner.');
        }
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        if (!count($this->middlewares)) {
            throw new IncompleteRunException('HttpRunner middleware must return a response before the end.');
        }

        $middleware = array_shift($this->middlewares);
        
        return call_user_func_array($middleware, [$request, $response, $this]);
    }
}
