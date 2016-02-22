<?php

namespace Laasti\Core\Providers;

use League\BooBoo\Runner;
use League\Container\ServiceProvider\AbstractServiceProvider;


class BooBooProvider extends AbstractServiceProvider
{

    protected $provides = [
        'error_handler',
        'League\BooBoo\Runner',
        'League\BooBoo\Formatter\HtmlTableFormatter',
        'League\BooBoo\Handler\LogHandler'
    ];
    
    protected $defaultConfig = [
        'pretty_page' => null,
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
        
        $di->add('League\BooBoo\Handler\LogHandler')->withArgument('Psr\Log\LoggerInterface');
        $di->add('League\BooBoo\Formatter\HtmlTableFormatter');
        
        $di->share('League\BooBoo\Runner', function() use ($di, $config) {
            $runner = new Runner();
            foreach ($config['formatters'] as $containerKey => $error_level) {
                $formatter = $di->get($containerKey);
                $formatter->setErrorLimit($error_level);
                $runner->pushFormatter($formatter);
            }
            foreach ($config['handlers'] as $containerKey) {
                $handler = $di->get($containerKey);
                $runner->pushHandler($handler);
            }
            if (isset($config['pretty_page'])) {
                $runner->setErrorPageFormatter($di->get($config['pretty_page']));
            }
            return $runner;
        });
        $di->add('error_handler', function() use ($di) {
            return [$di->get('League\BooBoo\Runner'),'register'];
        });
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
