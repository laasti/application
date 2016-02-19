<?php


namespace Laasti\Application\Middlewares;

interface StackBuilderInterface
{
    /**
     * Pushes a middleware at the end of the collection
     * Parameter should be a callable, but the implementation might differ (using a container for example, and passing container keys as middleware)
     * @param callable|mixed $middleware
     */
    public function push($middleware);

    /**
     * Unshifts a middleware at the beginning of the collection
     * Parameter should be a callable, but the implementation might differ (using a container for example, and passing container keys as middleware)
     * @param callable|mixed $middleware
     */
    public function unshift($middleware);

    /**
     * Overwrites all middlewares with the given set
     * @param array $middlewares
     */
    public function setMiddlewares(array $middlewares);

    /**
     * @return callable Must always return a callable, the first middleware
     */
    public function create();
}
