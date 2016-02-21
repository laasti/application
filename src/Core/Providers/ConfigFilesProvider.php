<?php

namespace Laasti\Core\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ConfigFilesProvider extends AbstractServiceProvider
{
    
    protected $provides = [
        'config',
        'Noodlehaus\Config'
    ];
    
    public function register()
    {
        $this->getContainer()->share('Noodlehaus\Config', 'Noodlehaus\Config')->withArgument('config_files');
        $this->getContainer()->share('config', 'Noodlehaus\Config')->withArgument('config_files');
    }
}
