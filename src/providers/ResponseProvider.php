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
class ResponseProvider extends ServiceProvider
{

    protected $provides = [
        'Laasti\Response\ResponderInterface'
    ];
    
    protected $defaultConfig = [
        'responder' => [
            'Laasti\Response\Responder', [
                'Dflydev\DotAccessData\DataInterface', 'Laasti\Response\Engines\TemplateEngineInterface',
                'Symfony\Component\HttpFoundation\Response', 'Symfony\Component\HttpFoundation\JsonResponse',
                'Symfony\Component\HttpFoundation\RedirectResponse', 'Laasti\Response\ViewResponse'
            ]
        ],
        'template_engine' => 'Laasti\Response\Engines\PlainPhp',
        'locations' => []
    ];

    public function register()
    {
        $di= $this->getContainer();

        $config = $this->defaultConfig;
        if (isset($di['Response.config']) && is_array($di['Response.config'])) {
            $config = array_merge($config, $di['Response.config']);
        }
        
        if (!$di->isRegistered('Laasti\Response\Engines\TemplateEngineInterface')) {
            $di->add('Laasti\Response\Engines\TemplateEngineInterface', $config['template_engine'])->withArguments($config['locations']);
        }
        $di->add('Symfony\Component\HttpFoundation\Response');
        $di->add('Symfony\Component\HttpFoundation\JsonResponse');
        if (!$di->isRegistered('Dflydev\DotAccessData\DataInterface')) {
            $di->add('Dflydev\DotAccessData\DataInterface', 'Dflydev\DotAccessData\Data');
        }

        if (!$di->isRegistered('Laasti\Response\ViewResponse')) {
            $di->add('Laasti\Response\ViewResponse', 'Laasti\Response\ViewResponse')->withArguments(['Laasti\Response\Engines\TemplateEngineInterface']);
        }
        
        list($responder_class, $responder_args) = $config['responder'];
        $di->add('Laasti\Response\ResponderInterface', $responder_class, true)->withArguments($responder_args);
    }

}
