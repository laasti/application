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
class WhoopsProvider extends ServiceProvider
{

    protected $provides = [
        'Whoops\Run',
        'Whoops\Handler\HandlerInterface'
    ];

    public function register()
    {
        $c = $this->getContainer();
        
        //Default error handler
        if (!$c->isRegistered('Whoops\Handler\HandlerInterface')) {
            $c->add('Whoops\Handler\HandlerInterface', 'Whoops\Handler\PrettyPageHandler');
        }
        //TODO: Should provide an interface instead so we can swap if we need to
        $c->add('Whoops\Run', function() use ($c) {
            $run = new \Whoops\Run;
            $handler = $c->get('Whoops\Handler\HandlerInterface');
            $run->pushHandler($handler);
            $run->register();
            return $run;
        }, true);
    }

}
