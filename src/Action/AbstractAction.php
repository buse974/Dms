<?php

namespace Dms\Action;

use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractAction implements RequestHandlerInterface
{
    protected $headers;
    protected $service_dms;
    
    public function __construct($headers, $service_dms)
    {
        $this->headers = $headers;
        $this->service_dms = $service_dms;
    }
}
