<?php

namespace Laasti\Application\Providers;

class ConfigFilesProvider extends \League\Container\ServiceProvider
{
    
    protected $provides = [
        'config',
        'Noodlehaus\Config'
    ];
    
    public function register()
    {
        $this->getContainer()->share('Noodlehaus\Config', 'Noodlehaus\Config')->withArgument('config_files');
        $this->getContainer()->share('config', 'Noodlehaus\Config');
    }
}
