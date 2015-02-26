<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @author Sonia
 */
interface MiddlewareInterface
{

    /**
     *
     * @param Request $request
     * @return Request|Response
     */
    public function handle(Request $request);

    public function terminate(Request $request, Response $response);
}
