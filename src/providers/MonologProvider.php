<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Providers;

use League\Container\ServiceProvider;

/**
 * Description of WhoopsProvider
 *
 * @author Sonia
 */
class MonologProvider extends ServiceProvider
{

    protected $provides = [
        'Psr\Log\LoggerInterface'
    ];
    
    protected $defaultConfig = [
        'Default' => [
            'Monolog\Handler\BrowserConsoleHandler' => [\Monolog\Logger::DEBUG]
        ]
    ];

    public function register()
    {
        $di = $this->getContainer();
        if (isset($di['Monolog.config']) && is_array($di['Monolog.config'])) {
            $config = $di['Monolog.config'];
        } else {
            $config = $this->defaultConfig;
        }
        
        foreach ($config as $channel => $handlers) {
            //Default error handler
            $di->add('Monolog.Logger.'.$channel, $this->createLogger($channel, $handlers), true);
        }
        
        if (!$di->isRegistered('Psr\Log\LoggerInterface')) {
            $channels = array_keys($config);
            $di->add('Psr\Log\LoggerInterface', $di->get('Monolog.Logger.'.array_shift($channels)));
        }
    }
    
    protected function createLogger($channel, $handlers) {
        $di = $this->getContainer();
        $logger = new \Monolog\Logger($channel);
        foreach ($handlers as $class => $arguments) {
            $di->add($class)->withArguments($arguments);
            $logger->pushHandler($di->get($class));
        }
        return $logger;
    }

}
