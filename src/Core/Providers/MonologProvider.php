<?php

namespace Laasti\Core\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Monolog\Logger;


class MonologProvider extends AbstractServiceProvider
{

    protected $provides = [
        'Psr\Log\LoggerInterface'
    ];
    
    protected $defaultConfig = [
        'channels' => [
            'default' => [
                'Monolog\Handler\BrowserConsoleHandler' => [Logger::DEBUG]
            ]
        ]
    ];

    public function register()
    {
        $di = $this->getContainer();
        $config = $this->getConfig();
        
        foreach ($config['channels'] as $channel => $handlers) {
            $di->add('monolog.channels.'.$channel, $this->createLogger($channel, $handlers), true);
        }
        
        $channels = array_keys($config['channels']);
        $di->add('Psr\Log\LoggerInterface', $di->get('monolog.channels.'.array_shift($channels)));
    }
    
    protected function createLogger($channel, $handlers) 
    {
        $di = $this->getContainer();
        $logger = new Logger($channel);
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
    
    protected function getConfig()
    {
        $di = $this->getContainer();
        $diConfig = $di->get('config');
        if (isset($diConfig['monolog']) && is_array($diConfig['monolog'])) {
            $config = array_merge($this->defaultConfig, $diConfig['monolog']);
        } else {
            $config = $this->defaultConfig;
        }
        
        return $config;
    }

    public function provides($alias = null)
    {
        $channels = array_keys($this->getConfig());
        if (!is_null($alias)) {
            if (in_array($alias, $this->provides)) {
                return true;
            }
            foreach ($channels as $channel) {
                if ($alias === 'monolog.channels.'.$channel) {
                    return true;
                }
            }
        }

        $aliases = [];
        foreach ($channels as $channel) {
            $aliases[] = 'monolog.channels.'.$channel;
        }

        return array_merge($this->provides, $aliases);
    }

}