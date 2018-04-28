<?php

namespace Dms\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Dms\Document\Document;
use Zend\Diactoros\Response\JsonResponse;

class FileCopyAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $body = $request->getParsedBody();
        $document = [];
        $document['support'] = Document::SUPPORT_FILE_BUCKET_STR;
        $document['coding'] = 'binary';
        $document['data'] =  $body['object'];
        $document['name'] = $body['name'];
        $document['type'] = isset($body['type'])?$body['type']:null;
        $document['weight'] = isset($body['size'])?$body['size']:null;
        
        $doc = $container->get(\Dms\Service\DmsService::class)->add($document);
        
        return new JsonResponse(['id'=>$doc], 200, $this->headers);
    }
}
