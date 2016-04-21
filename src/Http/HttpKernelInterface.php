<?php

namespace Laasti\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpKernelInterface
{

    public function run(RequestInterface $request, ResponseInterface $response);
}
