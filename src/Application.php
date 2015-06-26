<?php

namespace Laasti;

//TODO: provide my own interfaces as I don't use anything else from the HTTP Kernel package
//use Laasti\Services\StackInterface;
//use Laasti\Services\RouteCollectionInterface;
use Symfony\Component\HttpFoundation\Request;

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
    protected $config = [
        'di' => [
            'Monolog.config' => [
                'Pixms' => [
                    'Monolog\Handler\BrowserConsoleHandler' => [\Monolog\Logger::DEBUG]
                ]
            ]
        ],
        'system_providers' => [
            'Laasti\Providers\SymfonySessionProvider',
            'Laasti\Providers\MonologProvider',
            'Laasti\Providers\BooBooProvider',
            'Laasti\Providers\FlySystemProvider',
            'Laasti\Providers\SpotProvider',
            'Laasti\Providers\ValitronProvider',
            'Laasti\Providers\GregwarImageProvider',
            'Laasti\Providers\MailerProvider',
            'Laasti\Providers\ResponseProvider',
            'Laasti\Providers\SymfonyTranslationProvider',
        ],
        'routes' => [],
        'middlewares' => [
            'Laasti\Route\Middlewares\RouteMiddleware',
            'Laasti\Route\Middlewares\ControllerMiddleware'
        ],
        'error_handler' => ['League\BooBoo\Runner', 'register'],
    ];

    public function __construct($config = [], $factory = null)
    {
        $this->config = array_merge($this->config, $config);
        
        $di_config = isset($config['di']) ? $config['di'] : [];
        parent::__construct(['di' => $di_config], $factory);
        
        $this->loadServiceProviders($this->config['system_providers']);
        $this->loadServiceProviders($this->config['providers']);
        if (isset($this->config['error_handler'])) {
            $this->registerErrorHandler($this->config['error_handler']);
        }

        //Make sure the app is the container, and only one exists
        $this->add('League\Container\ContainerInterface', $this, true);
        $this->add('League\Container\Container', $this, true);
    }

    public function getRouter() {

        if (is_null($this->router)) {
            $this->add('Laasti\Route\RouteCollector', null, true)->withArguments([$this->getRoutes(), $this]);
            $this->router = $this->get('Laasti\Route\RouteCollector');
        }

        return $this->router;
    }

    public function getRoutes()
    {
        if (is_null($this->routes)) {
            $this->add('League\Route\RouteCollection', null, true)->withArgument($this);
            $this->routes = $this->get('League\Route\RouteCollection');
            $this->routes->setStrategy($this->get('Laasti\Route\Strategies\TwoStepControllerStrategy'));
            $this->addRoutesFromConfig($this->config['routes']);
        }

        return $this->routes;
    }

    public function getStack()
    {
        if (is_null($this->stack)) {
            $this->add('Laasti\Stack\StackInterface', 'Laasti\Stack\ContainerStack')->withArgument($this);
            $this->stack = $this->get('Laasti\Stack\ContainerStack');
            $this->addMiddlewaresFromConfig();
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
        
        if (is_null($this->routes)) {
            $this->getRoutes();
        }

        $response = $this->getStack()->execute($request);
        $response->send();

        $this->getStack()->close($request, $response);
    }
    
    public static function loadEnvironment($dir) 
    {
        $dotenv = new \Dotenv\Dotenv($dir);
        $dotenv->load();
    }
    
    public function addServiceProvider($provider)
    {
        if (is_string($provider)) {
            $provider = $this->get($provider);
        }
        if (! $provider instanceof \League\Container\ServiceProvider) {
            throw new \InvalidArgumentException(
                'When registering a service provider, you must provide either and instance of ' .
                '[\League\Container\ServiceProvider] or a fully qualified class name'
            );
        }
        
        $provider->setContainer($this);
        
        if ($provider instanceof \Laasti\Providers\RoutableProviderInterface) {
            $this->addRoutesFromConfig($provider->getRoutes());
        }

        $this->providers[] = $provider;

        return $this;
    }
    
    protected function loadServiceProviders($providers = []) {
        foreach ($providers as $provider) {
            $this->addServiceProvider($provider);
        }
        return $this;
    }
    
    protected function addRoutesFromConfig($config) {
        foreach ($config as $route) {
            call_user_func_array(array($this->getRoutes(), 'addRoute'), $route);
        }
        return $this;
    }
    
    protected function addMiddlewaresFromConfig() {
        foreach ($this->config['middlewares'] as $middleware) {
            $this->stack->push($middleware);
        }
        return $this;
    }
    
    protected function registerErrorHandler($callback) {
        if (is_array($callback)) {
            $callback[0] = $this->get($callback[0]);
        }
        
        call_user_func($callback);
        
        return $this;
    }
}
