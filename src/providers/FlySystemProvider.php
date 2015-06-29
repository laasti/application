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
class FlySystemProvider extends ServiceProvider
{

    protected $provides = [
        'League\Flysystem\MountManager',
        'League\Flysystem\FilesystemInterface',
    ];
    
    protected $defaultConfig = [
        'default' => ['League\Flysystem\Adapter\Local', []],
        /*
         * Advanced example where MyS3Client is a registered object in the container
        'aws' => [
            'League\Flysystem\AwsS3v2\AwsS3Adapter' => ['MyS3Client', 'bucket-name', 'optional-prefix']
        ]
         */
    ];

    public function register()
    {
        $di= $this->getContainer();
        
        
        if (isset($di['FlySystem.config']) && is_array($di['FlySystem.config'])) {
            $config = $di['FlySystem.config'];
        } else {
            $config = $this->defaultConfig;
        }
        $di->add('League\Flysystem\Filesystem');
        $di->add('League\Flysystem\MountManager', function() use ($di, $config) {
            $manager = new \League\Flysystem\MountManager;
            foreach ($config as $mount => $adapter_config) {
                $di->add($adapter_config[0])->withArguments(isset($adapter_config[1]) ? $adapter_config[1] : null);
                $adapter = $di->get($adapter_config[0], isset($adapter_config[1]) ? $adapter_config[1] : null);
                $filesystem = $di->get('League\Flysystem\Filesystem', [$adapter]);
                $manager->mountFilesystem($mount, $filesystem);
            }
            return $manager;
        }, true);
        
        if (!$di->isRegistered('League\Flysystem\FilesystemInterface')) {
            //The first filesystem is used by default
            $di->add('League\Flysystem\FilesystemInterface', function () use ($di, $config) {
                $manager = $di->get('League\Flysystem\MountManager');
                $filesystems = array_keys($config);
                return $manager->getFilesystem(array_shift($filesystems));
            });
        }
    }

}
