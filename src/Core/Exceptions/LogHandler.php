<?php

namespace Laasti\Core\Exceptions;

class LogHandler extends \League\BooBoo\Handler\LogHandler
{    
    public function handle(\Exception $e)
    {

        if ($e instanceof \ErrorException) {
            $this->handleErrorException($e);
            return;
        }
        $msg = 'Uncaught exception "%s" with message "%s" in %s on line %n. %s';
        $this->logger->critical(sprintf($msg, get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()));
    }
}
