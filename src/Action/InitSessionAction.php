<?php

namespace Dms\Action;

use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class InitSessionAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return new JsonResponse(['result' => true], 200, $this->headers);
    }

}
