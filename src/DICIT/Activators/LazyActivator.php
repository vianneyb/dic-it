<?php
namespace DICIT\Activators;

use DICIT\Activator;
use DICIT\Container;

class LazyActivator implements Activator
{

    private $activator;

    public function __construct(Activator $activator)
    {
        $this->activator = $activator;
    }

    public function createInstance(Container $container, $serviceName, array $serviceConfig)
    {
        if (! isset($serviceConfig['lazy']) || ! $serviceConfig['lazy']) {
            return $this->activator->createInstance($container, $serviceName, $serviceConfig);
        }

        $activator = $this->activator;
        $factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory();

        $proxy = $factory->createProxy($serviceConfig['class'],
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($activator, $container,
            $serviceName, $serviceConfig)
            {
                $wrappedObject = $activator->createInstance($container, $serviceName, $serviceConfig);
                $initializer = null;

                return true;
            });

        return $proxy;
    }
}
