<?php

namespace Dms\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Dms\Model\Dms;
use Zend\View\Model\JsonModel;
use Dms\Service\DmsService as Sp;
use Dms\Document\Document;

class DocumentController extends AbstractActionController
{
    public function getAction()
    {
        $document = null;

        if (null === ($file = $this->params('file', null))) {
            throw new \Exception('file id does not exist');
        }
        try {
            $document = $this->getManagerDms()->loadDocument($file)->getDocument();
        } catch (\Exception $e) {
            try {
                preg_match('/(?P<id>\w+)($)?(-(?P<size>\w+)($)?)?(\[(?P<page>\d+)\]($)?)?(.*\.(?P<fmt>\w+)$)?/', $file, $matches, PREG_OFFSET_CAPTURE);

                $this->getManagerDms()->loadDocument($matches['id'][0]);
                try {
                    $this->getManagerDms()->setSize((isset($matches['size']) && !empty($matches['size'][0])) ? $matches['size'][0] : null);
                    $this->getManagerDms()->setPage((isset($matches['page']) && !empty($matches['page'][0])) ? $matches['page'][0] : null);
                    $this->getManagerDms()->setFormat((isset($matches['fmt']) && !empty($matches['fmt'][0])) ? $matches['fmt'][0] : null);

                    $document = $this->getManagerDms()->writeFile($file)->getDocument();
                } catch (\Exception $e) {
                    throw $e;
                }
            } catch (\Exception $e) {
                $document = $e->getMessage();
            }
        }

        if ($document instanceof Document) {
            header('HTTP/1.0 200');
            header('Content-type: '.((null !== $document->getType()) ? $document->getType() : 'application/octet-stream'));
            header('Content-Transfer-Encoding: '.$document->getEncoding());
            header('Content-Disposition: '.sprintf('filename=%s', ((null === $document->getName()) ? (substr($file, -1 * strlen($document->getFormat())) === $document->getFormat()) ? $file : $file.'.'.$document->getFormat() : $document->getName())));
            header('Accept-Ranges: bytes');

            $print = true;
            if (isset($_SERVER['HTTP_RANGE'])) {
                $print = array();
                $range = $_SERVER['HTTP_RANGE'];
                $pieces = explode('=', $range);
                $seek = explode('-', trim($pieces[1]));
                $print['start'] = intval($seek[0]);
                $print['end'] = intval((!empty($seek[1]) ? $seek[1] : ($document->getWeight() - 1)));
                $size = ($print['end'] - $print['start'] + 1);
                header('HTTP/1.0 206');
                header('content-length: '.$size);
                header('Content-Range: bytes '.$print['start'].'-'.$print['end'].'/'.$document->getWeight());
            } else {
                header('content-length: '.$document->getWeight());
            }

            $document->getDatas($print);
        }

        return $this->getResponse()->setContent($document);
    }

    public function getDownloadAction()
    {
        $document = null;

        if (null === ($file = $this->params('file', null))) {
            throw new \Exception('file id does not exist');
        }

        try {
            $document = $this->getManagerDms()->loadDocument($file)->getDocument();
        } catch (\Exception $e) {
            $content = $e->getMessage();
        }

        $content = $document->getDatas();
        $headers = $this->getResponse()->getHeaders();
        $headers->addHeaderLine('Content-type', 'application/octet-stream');
        $headers->addHeaderLine('Content-Transfer-Encoding', $document->getEncoding());
        $headers->addHeaderLine('Content-Length', strlen($content));
        $name = $document->getName();
        $headers->addHeaderLine('Content-Disposition', sprintf('filename=%s', ((empty($name)) ? $file.'.'.$document->getFormat() : $name)));

        return $this->getResponse()->setContent($content);
    }

    public function getTypeAction()
    {
        $content = null;

        if (null !== ($file = $this->params('file', null))) {
            try {
                $m_document = $this->getManagerDms()->loadDocument($file)->getDocument();
                if ($m_document) {
                    $type = $m_document->getType();
                    $content = (empty($type) ? $m_document->getFormat() : $type);
                }
            } catch (\Exception $e) {
                $content = $e->getMessage();
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function getFormatAction()
    {
        $content = null;

        if (null !== ($file = $this->params('file', null))) {
            try {
                $m_document = $this->getManagerDms()->loadDocument($file)->getDocument();
                if ($m_document) {
                    $content = $m_document->getFormat();
                }
            } catch (\Exception $e) {
                $content = $e->getMessage();
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function getNameAction()
    {
        $content = null;

        if (null !== ($file = $this->params('file', null))) {
            try {
                $m_document = $this->getManagerDms()->loadDocument($file)->getDocument();
                $content = $m_document->getName();
            } catch (\Exception $e) {
                $content = $e->getMessage();
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function getDescriptionAction()
    {
        $content = null;

        if (null !== ($file = $this->params('file', null))) {
            try {
                $m_document = $this->getManagerDms()->loadDocument($file)->getDocument();
                $content = $m_document->getDescription();
            } catch (\Exception $e) {
                $content = $e->getMessage();
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function saveAction()
    {
        $dms_conf = $this->getServiceLocator()->get('Config')['dms-conf'];
        if (isset($dms_conf['headers']) && is_array($dms_conf['headers'])) {
            foreach ($dms_conf['headers'] as $key => $value) {
                $this->getResponse()->getHeaders()->addHeaderLine($key, $value);
            }
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $ret = array();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $files = $request->getFiles()->toArray();
            foreach ($files as $name_file => $file) {
                $document['support'] = Document::SUPPORT_FILE_MULTI_PART_STR;
                $document['coding'] = 'binary';
                $document['data'] = $file;
                $document['name'] = $file['name'];
                $document['type'] = $file['type'];
                $document['weight'] = $file['size'];
                $doc = $this->getServiceDms()->add($document);
                $ret[$name_file] = $doc;
            }
        }

        return new JsonModel($ret);
    }

    public function progressAction()
    {
        $dms_conf = $this->getServiceLocator()->get('Config')['dms-conf'];
        if (isset($dms_conf['headers']) && is_array($dms_conf['headers'])) {
            foreach ($dms_conf['headers'] as $key => $value) {
                $this->getResponse()->getHeaders()->addHeaderLine($key, $value);
            }
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return new JsonModel(Sp::progressAction($this->params()->fromPost('uploadUID')));
    }

    public function initSessionAction()
    {
        $dms_conf = $this->getServiceLocator()->get('Config')['dms-conf'];
        if (isset($dms_conf['headers']) && is_array($dms_conf['headers'])) {
            foreach ($dms_conf['headers'] as $key => $value) {
                $this->getResponse()->getHeaders()->addHeaderLine($key, $value);
            }
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return new JsonModel(array('result' => true));
    }

    /**
     * @return \Dms\Document\Manager
     */
    public function getManagerDms()
    {
        return $this->getServiceLocator()->get('dms.manager');
    }

    /**
     * @return \Dms\Service\DmsService
     */
    public function getServiceDms()
    {
        return $this->getServiceLocator()->get('dms.service');
    }
}
