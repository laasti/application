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
class SymfonyTranslationProvider extends ServiceProvider
{

    protected $provides = [
        'Symfony\Component\Translation\Translator'
    ];
    
    protected $defaultConfig = [
        'default_locale' => 'en',
        'fallback_locales' => ['en'],
        'message_selector' => 'Symfony\Component\Translation\MessageSelector',
        'loaders' => ['array' => 'Symfony\Component\Translation\Loader\ArrayLoader'],
        'resources' => [
            'en' => [
                ['array', ['hello_world' => 'Hello']]
            ]
        ],
    ];
    
    public function register()
    {
        $di= $this->getContainer();
        
        $config = $this->defaultConfig;
        if (isset($di['SymfonyTranslation.config']) && is_array($di['SymfonyTranslation.config'])) {
            $config = array_merge($config, $di['SymfonyTranslation.config']);
        }
        if (!$di->isRegistered($config['message_selector'])) {
            $di->add($config['message_selector']);
        }
        if (!$di->isRegistered($config['loaders']['array'])) {
            $di->add($config['loaders']['array']);
        }

        $di->add('Symfony\Component\Translation\Translator', function() use ($di, $config) {
            $selector = $di->get($config['message_selector']);
            $translator = new \Symfony\Component\Translation\Translator($config['default_locale'], $selector);
            $translator->setFallbackLocales($config['fallback_locales']);
            foreach ($config['loaders'] as $name => $class) {
                $translator->addLoader($name, $di->get($class));
            }
            foreach ($config['resources'] as $locale => $resources) {
                foreach ($resources as $config) {
                    //Default domain for symfony
                    if (!isset($config[2])) {
                        $config[2] = 'messages';
                    }
                    list($loader, $data, $domain) = $config;
                    $translator->addResource($loader, $data, $locale, $domain);
                }
            }
            return $translator;
        }, true);

    }

}
