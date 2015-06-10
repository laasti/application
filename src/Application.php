<?php

namespace Laasti;

//TODO: provide my own interfaces as I don't use anything else from the HTTP Kernel package
//use Laasti\Services\StackInterface;
//use Laasti\Services\RouteCollectionInterface;

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
    protected $router;

    public function __construct($config = [], $factory = null)
    {
        parent::__construct([], null);

        //Make sure the app is the container, and only one exists
        $this->add('League\Container\ContainerInterface', $this, true);
        $this->add('League\Container\Container', $this, true);
    }

    public function getRouter() {

        if (is_null($this->router)) {
            $this->add('Laasti\Route\RouteCollector', null, true)->withArguments(['', $this->getRoutes(), $this]);
            $this->router = $this->get('Laasti\Route\RouteCollector');
        }

        return $this->router;
    }

    public function getRoutes()
    {
        if (is_null($this->routes)) {
            $this->add('League\Route\RouteCollection', null, true)->withArgument($this);
            $this->routes = $this->get('League\Route\RouteCollection');
            $this->routes->setStrategy($this->get('Laasti\Route\Strategies\RouteStrategy'));
        }

        return $this->routes;
    }

    public function getStack()
    {
        if (is_null($this->stack)) {
            $this->add('Laasti\Stack\StackInterface', 'Laasti\Stack\ContainerStack')->withArgument($this);
            $this->stack = $this->get('Laasti\Stack\ContainerStack');
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
        if (is_null($request)) {
            $request_obj = $this->get('Symfony\Component\HttpFoundation\Request');
            $request = $request_obj::createFromGlobals();
            $this->add('Symfony\Component\HttpFoundation\Request', $request, true);
        }

        $response = $this->getStack()->execute($request);
        $response->send();

        $this->getStack()->close($request, $response);
    }
}
