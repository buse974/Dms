<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * DmsServiceFactory
 */
namespace Dms\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class DmsServiceFactory.
 */
class DmsFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return Dms
     *
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Dms($container->get(\Dms\Service\DmsService::class), $container->get('config')['dms-conf']);
    }
}
