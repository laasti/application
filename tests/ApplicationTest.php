<?php

namespace Laasti\Application\Test;

use Laasti\Http\Application;
use Laasti\Http\HttpKernel;
use League\Container\Container;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    
    public function testApplication()
    {
        $this->expectOutputString('Hello World');
        $emitter = $this->getMock('Laasti\Http\EmitterInterface');
        $emitter->expects($this->once())->method('emit')->will($this->returnCallback(function($response) {echo $response->getBody();}));
        $container = new Container;
        $container->add('config', []);
        $container->add('kernel', new HttpKernel(function($request, $response) {return $response;}, $emitter));
        $container->addServiceProvider(new \Laasti\Log\MonologProvider());
        $app = new Application($container);
        $app->run(new Request, new Response\TextResponse('Hello World'));
    }

}
