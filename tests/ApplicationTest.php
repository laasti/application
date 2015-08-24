<?php

namespace Laasti\Application\Test;

use Laasti\Application\Application;
use Laasti\Response\ResponderInterface;
use Laasti\Stack\StackInterface;
use League\BooBoo\Runner;
use League\Route\RouteCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testApplicationAsIs()
    {
        $app = new Application();
        $this->setExpectedException('League\Route\Http\Exception\NotFoundException');
        $app->run();
    }

    public function testApplicationAsIsWithCustomRequest()
    {
        $app = new Application();
        $this->setExpectedException('League\Route\Http\Exception\NotFoundException');
        $app->run(Request::create('/test'));
    }

    public function testApplicationBasicRouting()
    {
        $app = new Application();
        $controller = $this->getMockBuilder('MyController')->setMethods(['display'])->getMock();
        $controller->method('display')->will($this->returnValue(new Response('MY Response')));
        $app->add('MyController', $controller);
        $app->getRouter()->addRoute('GET', '/test', 'MyController::display');
        $this->expectOutputString('MY Response');
        $app->run(Request::create('/test'));
    }

    public function testApplicationContainerRequest()
    {
        $app = new Application();
        $controller = $this->getMockBuilder('MyController')->setMethods(['display'])->getMock();
        $controller->method('display')->will($this->returnValue(new Response('MY Response2')));
        $app->add('MyController', $controller);
        $app->getRouter()->addRoute('GET', '/test', 'MyController::display');
        $app->add('Symfony\Component\HttpFoundation\Request', Request::create('/test'), true);
        $this->expectOutputString('MY Response2');
        $app->run();
    }

    public function testApplicationRoutingConfig()
    {
        $config = [
            'routes' => [
                ['GET', '/test', 'MyController::display']
            ]
        ];
        $app = new Application($config);
        $controller = $this->getMockBuilder('MyController')->setMethods(['display'])->getMock();
        $controller->method('display')->will($this->returnValue(new Response('MY Response')));
        $app->add('MyController', $controller);
        $this->expectOutputString('MY Response');
        $app->run(Request::create('/test'));
    }

    public function testSystemServices()
    {
        $app = new Application;

        $this->assertTrue($app->getRouter() instanceof RouteCollection);
        $this->assertTrue($app->getStack() instanceof StackInterface);
        $this->assertTrue($app->getLogger() instanceof LoggerInterface);
        $this->assertTrue($app->get('League\BooBoo\Runner') instanceof Runner);
        $this->assertTrue($app->get('Laasti\Response\ResponderInterface') instanceof ResponderInterface);
    }

    public function testMiddlewareArray()
    {
        $config = [
            'middlewares' => [
                'Laasti\Route\DefineControllerMiddleware',
                ['Laasti\Route\CallControllerMiddleware', 3],
            ]
        ];
        $app = new Application($config);

        $controller = $this->getMockBuilder('MyController')->setMethods(['display'])->getMock();
        $controller->method('display')->will($this->returnValue(new Response('MY Response')));
        $request = Request::create('/test');
        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $middleware->expects($this->once())->method('prepare')->with($request, 3)
                ->will($this->returnValue(new Response('Test')));
        $app->add('MyController', $controller);
        $app->add('Laasti\Route\CallControllerMiddleware', $middleware);
        $this->expectOutputString('Test');
        $app->getRouter()->addRoute('GET', '/test', 'MyController::display');
        $app->run($request);
    }

}
