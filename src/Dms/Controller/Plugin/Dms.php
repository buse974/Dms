<?php
/**
 * 
 * github.com/buse974/Dms (https://github.com/buse974/Dms)
 *
 * Dms
 *
 */
namespace Dms\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Dms\Service\DmsService;

/**
 * Plugin Dms
 */
class Dms extends AbstractPlugin
{

    /**
     * @var DmsService
     */
    protected $service;
    
    /**
     * 
     * @var array
     */
    protected $options;

    /**
     * Constructor
     * 
     * @param DmsService $service
     * @param array $options
     */
    public function __construct(DmsService $service, $options)
    {
        $this->service = $service;
        $this->options = $options;
    }

    /**
     * Get Service Dms
     * 
     * @return DmsService
     */
    public function getService()
    {
        return $this->service;
    }
    
    /**
     * Get Array Hearders 
     * 
     * @return array
     */
    public function getHearders()
    {
        return $this->options['headers'];
    }
}