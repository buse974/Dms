<?php

namespace Dms\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ProgressBar\Upload\SessionProgress as Sp;
use Dms\Document\Document;

class DmsService implements ServiceManagerAwareInterface
{
    protected $service_manager;
    protected $document_manager;

    /**
     *
     * @param  array  $document
     * @return string token
     */
    public function add(array $document)
    {
        $this->getDocumentManager()->createDocument($document);
        $this->getDocumentManager()->decode();
        $this->getDocumentManager()->writeFile();
        $document = $this->getDocumentManager()->getDocument();

        return $document->getId();
    }

    /**
     *
     * @param  array  $document
     * @return string token
     */
    public function resize($size)
    {
        $this->getDocumentManager()->setSize($size);
        $this->getDocumentManager()->writeFile();
        $document = $this->getDocumentManager()->getDocument();

        return $document->getId();
    }

    public static function progressAction($id)
    {
        $progress = new Sp();

        return  $progress->getProgress($id);
    }

    /**
     *
     * @return \Dms\Document\Manager
     */
    public function getDocumentManager()
    {
        if (null === $this->document_manager) {
            $this->document_manager = $this->getServiceManager()->get('dms.manager');
        }

        return $this->document_manager;
    }
    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->service_manager;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $service_manager)
    {
        $this->service_manager = $service_manager;
    }
}
