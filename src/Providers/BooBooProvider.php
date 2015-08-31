<?php

namespace Laasti\Application\Providers;

use League\Container\ServiceProvider;

class BooBooProvider extends ServiceProvider
{

    protected $provides = [
        'League\BooBoo\Runner',
        'League\BooBoo\Formatter\HtmlTableFormatter',
        'League\BooBoo\Handler\LogHandler'
    ];
    
    protected $defaultConfig = [
        //How errors are displayed in the output
        'formatters' => [
            'League\BooBoo\Formatter\HtmlTableFormatter' => E_ALL
        ],
        //How errors are handled (logging, sending e-mails...)
        'handlers' => [
            'League\BooBoo\Handler\LogHandler'
        ]
    ];

    public function register()
    {
        $di = $this->getContainer();

        if (isset($di['config.error_handler']) && is_array($di['config.error_handler'])) {
            $config = array_merge($this->defaultConfig, $di['config.error_handler']);
        } else {
            $config = $this->defaultConfig;
        }
        
        if (!$di->isRegistered('League\BooBoo\Handler\LogHandler')) {
            $di->add('League\BooBoo\Handler\LogHandler')->withArgument('Psr\Log\LoggerInterface');
        }
        if (!$di->isRegistered('League\BooBoo\Formatter\HtmlTableFormatter')) {
            $di->add('League\BooBoo\Formatter\HtmlTableFormatter');
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
            return $runner;
        }, true);
    }

}
