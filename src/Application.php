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
            ],
            'Laasti\Stack\ContainerStack' => [
                'class' => 'Laasti\Stack\ContainerStack',
                'arguments' => ['League\Container\ContainerInterface'],
            ],
            'Laasti\Route\Middlewares\RouteMiddleware' => [
                'class' => 'Laasti\Route\Middlewares\RouteMiddleware',
                'arguments' => ['League\Route\RouteCollection']
            ],
            'Laasti\Route\Strategies\TwoStepControllerStrategy' => [
                'class' => 'Laasti\Route\Strategies\TwoStepControllerStrategy'
            ],
            'Laasti\Route\Middlewares\TwoStepControllerMiddleware' => [
                'class' => 'Laasti\Route\Middlewares\TwoStepControllerMiddleware',
                'arguments' => ['League\Container\ContainerInterface']
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
            'Laasti\Route\Middlewares\TwoStepControllerMiddleware'
        ],
        'error_handler' => ['League\BooBoo\Runner', 'register'],
    ];

    public function __construct($config = [], $factory = null)
    {
        $di_config = array_merge($this->config['di'], isset($config['di']) ? $config['di'] : []);
        $config['di'] = $di_config;
        $this->config = array_merge($this->config, $config);
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
    
    public function getLogger()
    {
        return $this->get('Psr\Log\LoggerInterface');
    }

    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request Request to process
     */
    public function run(Request $request = null)
    {
        if (is_null($request) && ! $this->isRegistered('Symfony\Component\HttpFoundation\Request')) {
            $request_obj =  new \Symfony\Component\HttpFoundation\Request;
            $request = $request_obj::createFromGlobals();
            $this->add('Symfony\Component\HttpFoundation\Request', $request, true);
        } else if (is_null($request)) {
            $request = $this->get('Symfony\Component\HttpFoundation\Request');
        }
        
        if (is_null($this->routes)) {
            $this->getRoutes();
        }

        $response = $this->getStack()->execute($request);
        $response->send();
        
        $this->getLogger()->debug('There are '.count($this->items).' items registered to the container.');
        $this->getLogger()->debug('There are '.count($this->singletons).' singletons registered to the container.');
        $this->getLogger()->debug('There are '.count($this->providers).' providers registered to the container.');

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
            $provider = new $provider;
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
        //TODO: Remove
        if (is_null($config)) {
            return $this;
        }
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
    
    protected function reflect($class)
    {
        $this->getLogger()->debug('Reflection was used on class "'.$class.'". You might want to avoid this in production.');
        return parent::reflect($class);
    }
}
