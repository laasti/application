<?php

namespace Laasti\Application\Providers;

use League\Container\ServiceProvider;

class ResponseProvider extends ServiceProvider
{

    protected $provides = [
        'Laasti\Response\ResponderInterface'
    ];
    protected $defaultConfig = [
        'class' => 'Laasti\Response\Responder',
        'arguments' => [
            'Dflydev\DotAccessData\DataInterface', 'Laasti\Response\Engines\TemplateEngineInterface'
        ],
        'template_engine' => 'Laasti\Response\Engines\PlainPhp',
        'locations' => []
    ];

    public function register()
    {
        $di = $this->getContainer();

        if (isset($di['config.response']) && is_array($di['config.response'])) {
            $config = array_merge($this->defaultConfig, $di['config.response']);
        } else {
            $config = $this->defaultConfig;
        }

        if (!$di->isRegistered('Laasti\Response\Engines\TemplateEngineInterface')) {
            $di->add('Laasti\Response\Engines\TemplateEngineInterface', $config['template_engine'])->withArgument($config['locations']);
        }

        if (!$di->isRegistered('Dflydev\DotAccessData\DataInterface')) {
            $di->add('Dflydev\DotAccessData\DataInterface', 'Dflydev\DotAccessData\Data');
        }

        if (!$di->isRegistered('Laasti\Response\ViewResponse')) {
            $di->add('Laasti\Response\ViewResponse', 'Laasti\Response\ViewResponse')->withArguments(['Laasti\Response\Engines\TemplateEngineInterface']);
        }

        $di->add('Laasti\Response\ResponderInterface', $config['class'], true)->withArguments($config['arguments']);
    }

}
