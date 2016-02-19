<?php

namespace Laasti\Application\Middlewares;

interface MiddlewareResolverInterface
{
    public function resolve($middlewareDefinition);
}
