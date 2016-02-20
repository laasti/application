<?php


namespace Laasti\Core;

interface ApplicationInterface
{
    /**
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer();

    public function setContainer(\Interop\Container\ContainerInterface $container);

    /**
     * @return \Laasti\Core\KernelInterface
     */
    public function getKernel();

    public function setKernel(\Laasti\Core\KernelInterface $kernel);

    /**
     * Must call the kernel's run method
     */
    public function run();

    /**
     * Returns application logger
     * @return LoggerInterface
     */
    public function getLogger();


    public function setLogger(\Psr\Log\LoggerInterface $logger);

    /**
     * Must return an associative array containing the application configuration registered to the config key in the container
     */
    public function getConfigArray();

    /**
     * Must return a specific config item
     */
    public function getConfig($key, $default);

    /**
     * Must set a specific config item
     */
    public function setConfig($key, $value);
}
