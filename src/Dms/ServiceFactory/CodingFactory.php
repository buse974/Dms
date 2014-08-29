<?php

namespace Dms\ServiceFactory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dms\Coding\CodingInterface;

class CodingFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return (substr($requestedName, -6)==='Coding');
    }

    /**
     * Create service with name
     *
     * @param  ServiceLocatorInterface           $serviceLocator
     * @param $name
     * @param $requestedName
     * @return \Dms\ServiceFactory\CodingFactory
    */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $factory = $serviceLocator->get(ucfirst($requestedName));
        if (!$factory instanceof CodingInterface) {
            throw new \Exception('not type CodingInterface');
        }

        return $factory;
    }

}
