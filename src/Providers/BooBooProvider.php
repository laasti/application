<?php

namespace Laasti\Application\Providers;

use League\Container\ServiceProvider;

class BooBooProvider extends ServiceProvider
{

    protected $provides = [
        'error_handler',
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
        $config = $this->getConfig();
        
        if (!$di->has('League\BooBoo\Handler\LogHandler')) {
            $di->add('League\BooBoo\Handler\LogHandler')->withArgument('Psr\Log\LoggerInterface');
        }
        if (!$di->has('League\BooBoo\Formatter\HtmlTableFormatter')) {
            $di->add('League\BooBoo\Formatter\HtmlTableFormatter');
        }
        
        $di->add('League\BooBoo\Runner', function() use ($di, $config) {
            $runner = new \League\BooBoo\Runner();
            foreach ($config['formatters'] as $containerKey => $error_level) {
                $formatter = $di->get($containerKey);
                $formatter->setErrorLimit($error_level);
                $runner->pushFormatter($formatter);
            }
            foreach ($config['handlers'] as $containerKey) {
                $handler = $di->get($containerKey);
                $runner->pushHandler($handler);
            }
            return $runner;
        }, true);
    }
    
    protected function getConfig()
    {
        $config = $this->getContainer()->get('config');
        if (isset($config['booboo']) && is_array($config['booboo'])) {
            $config = array_merge($this->defaultConfig, $config['booboo']);
        } else {
            $config = $this->defaultConfig;
        }
        
        return $config;
    }
}
