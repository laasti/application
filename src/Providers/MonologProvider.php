<?php

namespace Laasti\Application\Providers;

use League\Container\ServiceProvider;

class MonologProvider extends ServiceProvider
{

    protected $provides = [
        'Psr\Log\LoggerInterface'
    ];
    
    protected $defaultConfig = [
        'channels' => [
            'default' => [
                'Monolog\Handler\BrowserConsoleHandler' => [\Monolog\Logger::DEBUG]
            ]
        ]
    ];

    public function register()
    {
        $di = $this->getContainer();
        if (isset($di['config.logger']) && is_array($di['config.logger'])) {
            $config = array_merge($this->defaultConfig, $di['config.logger']);
        } else {
            $config = $this->defaultConfig;
        }
        
        foreach ($config['channels'] as $channel => $handlers) {
            //Default error handler
            $di->add('monolog.channel.'.$channel, $this->createLogger($channel, $handlers), true);
        }
        
        if (!$di->isRegistered('Psr\Log\LoggerInterface')) {
            $channels = array_keys($config['channels']);
            $di->add('Psr\Log\LoggerInterface', $di->get('monolog.channel.'.array_shift($channels)));
        }
    }
    
    protected function createLogger($channel, $handlers) {
        $di = $this->getContainer();
        $logger = new \Monolog\Logger($channel);
        foreach ($handlers as $class => $arguments) {
            if (is_string($arguments)) {
                $logger->pushHandler($di->get($arguments));
            } else {
                $di->add($class)->withArguments($arguments);
                $logger->pushHandler($di->get($class));
            }
        }
        return $logger;
    }

}
