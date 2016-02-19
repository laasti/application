<?php

namespace Laasti\Application\Middlewares;

class IORunner
{
    protected $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;

        if (!count($this->middlewares)) {
            throw new \InvalidArgumentException('You must at least define 1 middleware to use with IORunner.');
        }
    }

    public function __invoke($input, $output)
    {
        if (!count($this->middlewares)) {
            throw new IncompleteRunException('IORunner middleware must return a value before the end.');
        }

        $middleware = array_shift($this->middlewares);

        return call_user_func_array($middleware, [$input, $output, $this]);
    }
}
