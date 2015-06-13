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
class MailerProvider extends ServiceProvider
{

    protected $provides = [
        'Laasti\Mailer\Mailer',
        'Laasti\Mailer\Servers\ServerInterface',
        'Laasti\Mailer\Message',
    ];
    
    protected $defaultConfig = [
        'server' => 'Laasti\Mailer\Servers\NullServer',
        'server_args' => ['Psr\Log\LoggerInterface']
    ];

    public function register()
    {
        $di= $this->getContainer();

        if (isset($di['Mailer.config']) && is_array($di['Mailer.config'])) {
            $config = $di['Mailer.config'];
        } else {
            $config = $this->defaultConfig;
        }
        
        if (!$di->isRegistered('Laasti\Mailer\Servers\ServerInterface')) {
            $di->add('Laasti\Mailer\Servers\ServerInterface', $config['server'])->withArguments($config['server_args']);
        }
        
        $di->add('Laasti\Mailer\Mailer')->withArgument('Laasti\Mailer\Servers\ServerInterface');
    }

}
