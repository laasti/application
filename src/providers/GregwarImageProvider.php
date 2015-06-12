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
class GregwarImageProvider extends ServiceProvider
{

    protected $provides = [
        'Gregwar\Image\Image'
    ];

    protected $defaultConfig = [
        'cache_dir' => ''
    ];

    public function register()
    {
        $di = $this->getContainer();
        $config = $this->defaultConfig;
        if (isset($di['GregwarImage.config']) && is_array($di['GregwarImage.config'])) {
            $config = array_merge($config, $di['GregwarImage.config']);
        }

        $di->add('Gregwar\Image\Image', function($filepath = null, $w = null, $h = null) use ($config) {
            $image = new \Gregwar\Image\Image($filepath, $w, $h);
            $image->setCacheDir($config['cache_dir']);
            return $image;
        })->withArgument([]);
    }

}
