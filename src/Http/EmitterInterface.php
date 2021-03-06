<?php


namespace Laasti\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Prevents hard dependency upon Diactoros
 */
interface EmitterInterface
{
    public function emit(ResponseInterface $response);
}
