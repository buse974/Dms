<?php

namespace Dms\Storage;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;

abstract class AbstractStorage implements EventManagerAwareInterface, ServiceManagerAwareInterface, StorageInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     *
     * @var ServiceManager
     */
    protected $servicemanager;

    /**
     *
     * @var StorageOption
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = new StorageOption($options);
    }
    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->servicemanager = $serviceManager;

        return $this;
    }
}
