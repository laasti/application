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
        'responder' => ['Laasti\Response\Responder', ['Dflydev\DotAccessData\DataInterface', 'Laasti\Response\TemplateEngineInterface']],
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
        
        if (!$di->isRegistered('Laasti\Response\TemplateEngineInterface')) {
            $di->add('Laasti\Response\TemplateEngineInterface', $config['template_engine'])->withArguments($config['locations']);
        }

        if (!$di->isRegistered('Dflydev\DotAccessData\DataInterface')) {
            $di->add('Dflydev\DotAccessData\DataInterface', 'Dflydev\DotAccessData\Data');
        }
        
        list($responder_class, $responder_args) = $config['responder'];
        $di->add('Laasti\Response\ResponderInterface', $responder_class)->withArguments($responder_args);
    }

}
