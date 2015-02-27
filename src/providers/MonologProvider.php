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
        'Psr\Log\LoggerInterface',
        'Monolog\Handler\HandlerInterface'
    ];

    public function register()
    {
        $c = $this->getContainer();
        
        //Default error handler
        if (!$c->isRegistered('Monolog\Handler\HandlerInterface')) {
            $c->add('Monolog\Handler\HandlerInterface', 'Monolog\Handler\BrowserConsoleHandler');
        }
        $c->add('Psr\Log\LoggerInterface', function() use ($c) {
            $logger = new \Monolog\Logger('Laasti');
            $handler = $c->get('Monolog\Handler\HandlerInterface');
            $logger->pushHandler($handler);
            return $logger;
        }, true);
    }

}
