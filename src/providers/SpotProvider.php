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
class SpotProvider extends ServiceProvider
{

    protected $provides = [
        'Spot\Config',
        'Spot\Locator'
    ];

    protected $requires = [
        'db.dsn',
        'db.driver'
    ];

    public function register()
    {
        $c = $this->getContainer();

        //Default error handler
        if (!$c->isRegistered('Spot\Config')) {
            $c->add('Spot\Config', function() use ($c) {
                $cfg = new \Spot\Config();
                $cfg->addConnection($c['Spot.driver'], $c['Spot.dsn']);
                return $cfg;
            });
        }

        $c->add('Spot\Locator', function() use ($c) {
            $cfg = $c->get('Spot\Config');
            $spot = new \Spot\Locator($cfg);
            return $spot;
        }, true);
    }

}
