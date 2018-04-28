<?php

namespace Dms\Action;

use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Dms\Document\NoFileException;

class FileProgressAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return new JsonResponse(Sp::progressAction($request->getAttribute('uploadUID')), 200, $this->headers);
    }
}
