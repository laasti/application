<?php

namespace Laasti\Application;

use Laasti\Stack\StackInterface;
use League\Container\Container;
use League\Container\Definition\FactoryInterface;
use League\Route\RouteCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use TomPHP\ConfigServiceProvider\ConfigServiceProvider;

class Application extends Container
{

    /**
     * Application router
     * @var RouteCollection
     */
    protected $router;

    /**
     * Application stack
     * @var StackInterface
     */
    protected $stack;

    /**
     * Application config
     * @var array
     */
    protected $config = [
        'config' => [
            'error_handler' => [
                /* Do not display errors on screen.
                'formatters' => [
                    'League\BooBoo\Formatter\NullFormatter' => E_ALL
                ]
                 */
            ],
        ],
        'di' => [
            'League\Route\RouteCollection' => [
                'class' => 'League\Route\RouteCollection',
                'arguments' => ['League\Container\ContainerInterface'],
                'singleton' => true
            ],
            'League\Route\Strategy\StrategyInterface' => [
                 'class' => 'Laasti\Route\ControllerDefinitionStrategy'
            ],
            'Laasti\Route\DefineControllerMiddleware' => [
                'class' => 'Laasti\Route\DefineControllerMiddleware',
                'arguments' => ['League\Route\RouteCollection']
            ],
            'Laasti\Route\CallControllerMiddleware',
            'Laasti\Stack\ResolverInterface' => [
                'class' => 'Laasti\Stack\ContainerResolver',
                'arguments' => ['League\Container\ContainerInterface']
            ],
            'Laasti\Stack\StackInterface' => [
                'class' => 'Laasti\Stack\Stack',
                'arguments' => ['Laasti\Stack\ResolverInterface'],
                'singleton' => true
            ]
        ],
        'system_providers' => [
            'Laasti\Application\Providers\BooBooProvider',
            'Laasti\Application\Providers\MonologProvider',
            'Laasti\Application\Providers\ResponseProvider',
        ],
        'providers' => [],
        'routes' => [],
        'middlewares' => [
            'Laasti\Route\DefineControllerMiddleware',
            'Laasti\Route\CallControllerMiddleware',
        ],
        'error_handler' => ['League\BooBoo\Runner', 'register'],
    ];

    /**
     * Construction
     * @param array $config
     * @param FactoryInterface $factory
     */
    public function __construct($config = [], $factory = null)
    {
        $config['di'] = array_merge($this->config['di'], isset($config['di']) ? $config['di'] : []);
        $this->config = array_merge($this->config, $config);

        parent::__construct(['di' =>  $config['di']], $factory);

        $this->addServiceProvider(new ConfigServiceProvider($this->config['config']));
        $this->loadServiceProviders($this->config['system_providers']);
        $this->loadServiceProviders($this->config['providers']);
        
        if (isset($this->config['error_handler'])) {
            $this->registerErrorHandler($this->config['error_handler']);
        }
    }

    /**
     * Handles the request and delivers the response through the stack
     *
     * @param Request|null $request Request to process
     */
    public function run(Request $request = null)
    {
        if (is_null($request) && ($this->isRegistered('Symfony\Component\HttpFoundation\Request') || $this->isSingleton('Symfony\Component\HttpFoundation\Request'))) {
            $request = $this->get('Symfony\Component\HttpFoundation\Request');
        } elseif (is_null($request)) {
            $request_obj =  new Request;
            $request = $request_obj::createFromGlobals();
            $this->add('Symfony\Component\HttpFoundation\Request', $request, true);
        }

        //Make sure the router is initialized
        if (is_null($this->router)) {
            $this->getRouter();
        }

        $this->getStack()->execute($request);
        
        $this->getLogger()->debug('There are '.count($this->items).' items registered to the container.');
        $this->getLogger()->debug('There are '.count($this->singletons).' singletons registered to the container.');
        $this->getLogger()->debug('There are '.count($this->providers).' providers registered to the container.');
    }

    /**
     * Batch load service providers
     * @param array $providers
     * @return Application
     */
    protected function loadServiceProviders($providers = []) {
        foreach ($providers as $provider) {
            $this->addServiceProvider($provider);
        }
        return $this;
    }

    /**
     * Register error handler for PHP
     * @param callable $callback
     * @return Application
     */
    protected function registerErrorHandler($callback) {
        if (is_array($callback)) {
            $callback[0] = $this->get($callback[0]);
        }

        call_user_func($callback);

        return $this;
    }

    /**
     * Returns current Application stack
     * @return StackInterface
     */
    public function getStack()
    {
        if (is_null($this->stack)) {
            $this->stack = $this->get('Laasti\Stack\StackInterface');
            $this->pushMiddlewares($this->config['middlewares']);
        }

        return $this->stack;
    }

    /**
     * Batch adds middlewares
     * @param array $middlewares
     * @return Application
     */
    protected function pushMiddlewares($middlewares) {
        foreach ($middlewares as $middleware) {
            if (is_array($middleware)) {
                call_user_func_array([$this->stack, 'push'], $middleware);
            } else {
                $this->stack->push($middleware);
            }
        }
        return $this;
    }

    /**
     * Returns current application router
     * @return RouteCollection
     */
    public function getRouter()
    {
        if (is_null($this->router)) {
            $this->router = $this->get('League\Route\RouteCollection');
            $this->router->setStrategy($this->get('League\Route\Strategy\StrategyInterface'));
            $this->addRoutesFromConfig($this->config['routes']);
        }

        return $this->router;
    }

    /**
     * Batch add routes from application config
     * @param array $config
     * @return Application
     */
    protected function addRoutesFromConfig($config) {
        
        foreach ($config as $route) {
            call_user_func_array([$this->router, 'addRoute'], $route);
        }
        
        return $this;
    }

    /**
     * Returns application logger
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->get('Psr\Log\LoggerInterface');
    }

    /**
     * {@inheritdoc}
     *
     * Adds a log information when reflection is used
     */
    protected function reflect($class)
    {
        $this->getLogger()->debug('Reflection was used on class "'.$class.'". You might want to avoid this in production.');
        return parent::reflect($class);
    }
}
