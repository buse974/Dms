<?php

namespace Dms\Action;

use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Dms\Document\NoFileException;

class FileInfoAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $info = explode('/', $request->getUri()->getPath())[0];
        $content = null;
        try {
            $content = $this->service_dms->getInfo($request->getAttribute('file'), $info);
        } catch (NoFileException $e) {
            $content = $e->getMessage();
        }
        
        return new JsonResponse((string) $content);
    }

}