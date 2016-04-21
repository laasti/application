<?php

namespace Laasti\Log;

interface LoggerAwareInterface
{
    /**
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger);

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger();
}
