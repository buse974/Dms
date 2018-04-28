<?php

namespace Dms\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Dms\Document\NoFileException;
use Zend\Diactoros\Response\TextResponse;

class FileDownloadAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $content = null;
        try {
            $document = $this->service_dms->getDocument($request->getAttribute('file'));
            $content = $document->getDatas();
            $name = $document->getName();
            $headers = [
                'Content-type' => 'application/octet-stream',
                'Content-Transfer-Encoding' => $document->getEncoding(),
                'Content-Length' => "".strlen($content)."",
                'Content-Disposition' => sprintf('filename=%s', ((empty($name)) ? $file.'.'.$document->getFormat() : $name))
            ];
        } catch (NoFileException $e) {
            $content = $e->getMessage();
        }
        
        return new TextResponse($content, 200, $this->headers);
    }

}
