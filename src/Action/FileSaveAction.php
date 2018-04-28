<?php

namespace Dms\Action;

use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Dms\Document\Document;

class FileSaveAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $ret = [];
        $files = $request->getUploadedFiles();
        
        foreach ($files as $name_file => $file) {
            /** @var  \Zend\Diactoros\UploadedFile $file */
            $document['support'] = Document::SUPPORT_FILE_MULTI_PART_STR;
            $document['coding'] = 'binary';
            $document['data']  = $file;
            $document['name'] = $file->getClientFilename();
            $document['type'] = $file->getClientMediaType();
            $document['weight'] = $file->getSize();
            
            $doc = $this->service_dms->add($document);
            if (isset($ret[$name_file])) {
                if (is_array($ret[$name_file])) {
                    $ret[$name_file][] = $doc;
                } else {
                    $ret[$name_file] = [$ret[$name_file], $doc];
                }
            } else {
                $ret[$name_file] = $doc;
            }
        }
        
        return new JsonResponse($ret, 200, $this->headers);
    }

}
