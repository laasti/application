<?php

namespace Laasti\Application\Test;

use Laasti\Application\Http\HttpRunner;


class HttpRunnerTest extends \PHPUnit_Framework_TestCase
{

    public function testNoMiddlewares()
    {
        $this->setExpectedException('InvalidArgumentException');
        new HttpRunner();
    }

    public function testIncompleteRunException()
    {
        $this->setExpectedException('Laasti\Application\Middlewares\IncompleteRunException');
        $runner = new HttpRunner([
            function ($request, $response, $this) {
                return $this($request, $response);
            }
        ]);
        $runner(new \Zend\Diactoros\ServerRequest, new \Zend\Diactoros\Response);

    }

    public function testBaseHttpRunner()
    {
        $runner = new HttpRunner([
            function ($request, $response, $this) {
                return $response;
            }
        ]);
        $this->assertTrue($runner(new \Zend\Diactoros\ServerRequest, new \Zend\Diactoros\Response) instanceof \Zend\Diactoros\Response);
    }

    public function testMultipleHttpRunner()
    {
        $runner = new HttpRunner([
            function ($request, $response, $this) {
                $response = $response->withHeader('test', 'test');
                return $this($request, $response);
            },
            function ($request, $response, $this) {
                $response = $response->withHeader('test2', 'test2');
                return $response;
            }
        ]);
        $response = $runner(new \Zend\Diactoros\ServerRequest, new \Zend\Diactoros\Response);
        $this->assertTrue($response->getHeaderLine('test') === 'test');
        $this->assertTrue($response->getHeaderLine('test2') === 'test2');
    }

}
