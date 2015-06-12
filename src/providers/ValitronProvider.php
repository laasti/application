<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Providers;

use League\Container\ServiceProvider;
use Valitron\Validator;

/**
 * Description of WhoopsProvider
 *
 * @author Sonia
 */
class ValitronProvider extends ServiceProvider
{

    protected $provides = [
        'Valitron\Validator'
    ];

    protected $defaultConfig = [
        'rules' => [
            /*'alwaysFail' => [
                'callback' => function($field, $value, array $params) {return false;},
                'message' => 'Everything you do is wrong. You fail.'
            ]*/
        ],
        'language' => 'en',
        //'language_dir' => ''
    ];

    public function register()
    {
        $di = $this->getContainer();
        $config = $this->defaultConfig;
        if (isset($di['Valitron.config']) && is_array($di['Valitron.config'])) {
            $config = array_merge($config, $di['Valitron.config']);
        }
        if (isset($config['language_dir'])) {
            Validator::langDir($config['language_dir']);
        }
        
        Validator::lang($config['language']);
        
        foreach ($config['rules'] as $rulename => $rule_config) {
            Validator::addRule($rulename, $rule_config['callback'], $rule_config['message']);
        }

        $di->add('Valitron\Validator')->withArgument([]);
    }

}
