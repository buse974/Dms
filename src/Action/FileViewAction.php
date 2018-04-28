<?php

namespace Dms\Action;

use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Dms\Document\NoFileException;

class FileViewAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $this->service_dms->get($request->getAttribute('file'));
        } catch (NoFileException $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

}
