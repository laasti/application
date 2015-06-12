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
class SymfonySessionProvider extends ServiceProvider
{

    protected $provides = [
        'Symfony\Component\HttpFoundation\Session\SessionInterface',
        'Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface',
        'Symfony\Component\HttpFoundation\Session\Storage\MetadataBag',
    ];
    
    protected $defaultConfig = [
        'metadata_key' => '_laasti_meta',
        'cache_limiter' => 'nocache',
        'cookie_domain' => '',
        'cookie_httponly' => '',
        'cookie_lifetime' => '0',
        'cookie_path' => '/',
        'cookie_secure' => '',
        'entropy_file' => '',
        'entropy_length' => '0',
        'gc_divisor' => '100',
        'gc_maxlifetime' => '1440',
        'gc_probability' => '1',
        'hash_bits_per_character' => '4',
        'hash_function' => '0',
        'name' => 'LAASTI_PHPSESSID',
        'referer_check' => '',
        'serialize_handler' => 'php',
        'use_cookies' => '1',
        'use_only_cookies' => '1',
        'use_trans_sid' => '0',
        'upload_progress.enabled' => '1',
        'upload_progress.cleanup' => '1',
        'upload_progress.prefix' => 'upload_progress_',
        'upload_progress.name' => 'PHP_SESSION_UPLOAD_PROGRESS',
        'upload_progress.freq' => '1%',
        'upload_progress.min-freq' => '1',
        'url_rewriter.tags' => 'a=href,area=href,frame=src,form=,fieldset=',
    ];
    
    public function register()
    {
        $di= $this->getContainer();
        
        $config = $this->defaultConfig;
        if (isset($di['SymfonySession.config']) && is_array($di['SymfonySession.config'])) {
            $config = array_merge($config, $di['SymfonySession.config']);
        } 
        $di->add('Symfony\Component\HttpFoundation\Session\Storage\MetadataBag')->withArgument($config['metadata_key']);
        if (!$di->isRegistered('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface')) {
            $di->add('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface', 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage')->withArguments([$config, null, 'Symfony\Component\HttpFoundation\Session\Storage\MetadataBag']);
        }
        
        $di->add('Symfony\Component\HttpFoundation\Session\SessionInterface', 'Symfony\Component\HttpFoundation\Session\Session')->withArgument('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface');

    }

}
