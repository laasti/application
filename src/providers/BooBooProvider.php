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
class BooBooProvider extends ServiceProvider
{

    protected $provides = [
        'League\BooBoo\Runner'
    ];
    
    protected $defaultConfig = [
        'formatters' => [
            'League\BooBoo\Formatter\HtmlTableFormatter' => E_ALL
        ],
        'handlers' => [
            'League\BooBoo\Handler\LogHandler'
        ]
    ];

    public function register()
    {
        $di= $this->getContainer();
        
        if (!$di->isRegistered('League\BooBoo\Handler\LogHandler')) {
            $di->add('League\BooBoo\Handler\LogHandler')->withArgument('Psr\Log\LoggerInterface');
        }
        if (!$di->isRegistered('League\BooBoo\Formatter\HtmlTableFormatter')) {
            $di->add('League\BooBoo\Formatter\HtmlTableFormatter');
        }
        $config = $this->defaultConfig;
        
        if (isset($di['BooBoo.config']) && is_array($di['BooBoo.config'])) {
            $config = array_merge_recursive($config, $di['BooBoo.config']);
        } 
        
        $di->add('League\BooBoo\Runner', function() use ($di, $config) {
            $runner = new \League\BooBoo\Runner();

            foreach ($config['formatters'] as $class => $error_level) {
                $formatter = $di->get($class);
                $formatter->setErrorLimit($error_level);
                $runner->pushFormatter($formatter);
            }

            foreach ($config['handlers'] as $class) {
                $handler = $di->get($class);
                $runner->pushHandler($handler);
            }

            $runner->register();
            return $runner;
        }, true);
    }

}
