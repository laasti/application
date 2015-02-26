<?php

namespace Laasti;

//TODO: provide my own interfaces as I don't use anything else from the HTTP Kernel package
use Laasti\Services\ProfilerInterface;
use Laasti\Services\StackInterface;
use Laasti\Services\RouteCollectionInterface;

class Application extends \League\Container\Container
{

    protected $routes;
    protected $profiler;
    protected $stack;
    protected $middlewares = array();
    protected $middlewareInstances = array();

    public function __construct(ProfilerInterface $profiler, StackInterface $stack, RouteCollectionInterface $routes, $config = [], $factory = null)
    {
        parent::__construct($config, $factory);

        $this->profiler = $profiler;
        $this->stack = $stack;
        $this->routes = $routes;

        //Make sure the app is the container, and only one exists
        $this->add('League\Container\ContainerInterface', $this, true);
        $this->add('League\Container\Container', $this, true);
        $this->add('Laasti\Services\RouteCollectionInterface', $routes, true);
    }

    public function getRouter()
    {
        if (is_null($this->router)) {
            $this->router = $this->get('League\Route\RouteCollection');
        }

        return $this->router;
    }

    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request Request to process
     */
    public function run(Request $request = null)
    {
        $this->profiler->openSection();
        if (null === $request) {
            $request_obj = $this->get('Symfony\Component\HttpFoundation\Request');
            $request = $request_obj::createFromGlobals();
        }
        $this->profiler->start('middlewares_init');
        $this->initMiddlewares();
        $this->profiler->stop('middlewares_init');

        $this->profiler->start('handle_request');
        //TODO: Change awkward looping process for a middleware class
        $first = $this->middlewareInstances[0];
        $response = $first->handle($request);
        $response->send();
        $this->profiler->stop('handle_request');

        $this->profiler->start('terminate_request');
        $this->terminate($request, $response);
        $this->profiler->stop('terminate_request');
        $this->profiler->stopSection('Laasti\\Application::run');
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {

        //TODO Do something if we arrive no response was attached!
        throw new \Exception('404 no response was sent!');
    }

    public function terminate(Request $request, Response $response)
    {

        foreach ($this->middlewareInstances as $kernel) {
            // if prev kernel was terminable we can assume this middleware has already been called
            if ($kernel instanceof TerminableInterface) {
                $kernel->terminate($request, $response);
            }
        }
    }

    public function unshift(/* $kernelClass, $args... */)
    {
        if (func_num_args() === 0) {
            throw new \InvalidArgumentException("Missing argument(s) when calling push");
        }

        array_unshift($this->middlewares[], func_get_args());

        return $this;
    }

    public function addMiddleware(/* $kernelClass, $args... */)
    {
        $args = func_get_args();

        if ($args === 0) {
            throw new \InvalidArgumentException("Missing argument(s) when calling push");
        }

        //Automatically add middlewares to container
        if (!$this->isRegistered($args[0])) {
            $this->addDependency($args[0]);
        }

        $this->middlewares[] = $args;

        return $this;
    }

    public function addDependency($alias, $concrete = null, $singleton = false)
    {
        $this->add($alias, $concrete, $singleton);
        return $this;
    }

    private function initMiddlewares()
    {
        $middleware = $this;

        $reverse_middlewares = array_reverse($this->middlewares);
        foreach ($reverse_middlewares as $middleware_spec) {

            $middleware = $this->resolveMiddleware($middleware_spec, $middleware);

            array_unshift($this->middlewareInstances, $middleware);
        }
    }

    protected function resolveMiddleware($spec, $middleware)
    {
        $args = $spec;
        $firstArg = array_shift($args);

        if (is_callable($firstArg)) {
            $middleware = $firstArg($middleware);
        } else {
            $kernelClass = $firstArg;
            array_unshift($args, $middleware);
            $middleware = $this->get($kernelClass, $args);
        }

        return $middleware;
    }

}
