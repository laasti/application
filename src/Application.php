<?php

namespace Laasti;

//TODO: provide my own interfaces as I don't use anything else from the HTTP Kernel package
use Laasti\Services\StackInterface;
use Laasti\Services\RouteCollectionInterface;

class Application extends \League\Container\Container
{

    /**
     *
     * @var RouteCollectionInterface
     */
    protected $routes;

    /**
     *
     * @var StackInterface
     */
    protected $stack;

    public function __construct($config = [], $factory = null)
    {
        parent::__construct($config, $factory);

        //Make sure the app is the container, and only one exists
        $this->add('League\Container\ContainerInterface', $this, true);
        $this->add('League\Container\Container', $this, true);
    }

    public function getRoutes()
    {
        if (is_null($this->routes)) {
            $this->routes = $this->get('Laasti\Services\RouteCollectionInterface');
        }

        return $this->routes;
    }

    public function getStack()
    {
        if (is_null($this->stack)) {
            $this->stack = $this->get('Laasti\Services\StackInterface');
        }
        return $this->stack;
    }

    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request Request to process
     */
    public function run(Request $request = null)
    {
        if (null === $request) {
            $request_obj = $this->get('Symfony\Component\HttpFoundation\Request');
            $request = $request_obj::createFromGlobals();
        }

        $response = $this->getStack()->execute($request);
        $response->send();

        $this->getStack()->close($request, $response);
    }
}
