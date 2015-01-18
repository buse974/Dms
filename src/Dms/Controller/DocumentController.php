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

        if (null===($file=$this->params('file',null))) {
            throw new \Exception('file id does not exist');
        }

        try {
            $document = $this->getManagerDms()->loadDocument($file)->getDocument();
        } catch (\Exception $e) {
            try {
                preg_match('/(?P<id>\w+)($)?(-(?P<size>\w+)($)?)?(\[(?P<page>\d+)\]($)?)?(.*\.(?P<fmt>\w+)$)?/', $file, $matches, PREG_OFFSET_CAPTURE );

                $this->getManagerDms()->loadDocument($matches['id'][0])->getDocument();

                try {
                    $this->getManagerDms()->setSize((isset($matches['size']) && !empty($matches['size'][0])) ? $matches['size'][0] : null);
                    $this->getManagerDms()->setPage((isset($matches['page']) && !empty($matches['page'][0])) ? $matches['page'][0] : null);
                    $this->getManagerDms()->setFormat((isset($matches['fmt']) && !empty($matches['fmt'][0])) ? $matches['fmt'][0] : null);
                    $document = $this->getManagerDms()->writeFile($file)->getDocument();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                    exit();
                }
            } catch (\Exception $e) {
                $content = "file " . $file . " not found";
            }
        }

        if ($document) {
            $content = $document->getDatas();
            $headers = $this->getResponse()->getHeaders();

            if (null !== $document->getType()) {
                $headers->addHeaderLine('Content-type',$document->getType());
            } else {
                $headers->addHeaderLine('Content-type','application/octet-stream');
            }
            $headers->addHeaderLine("Content-Transfer-Encoding", $document->getEncoding());
            $headers->addHeaderLine('Content-Length', strlen($content));
            $name = $document->getName();
            $headers->addHeaderLine('Content-Disposition', sprintf('filename=%s', ((empty($name)) ? $file . '.' . $document->getFormat() : $name)));
        }

        return $this->getResponse()->setContent($content);
    }

    public function getDownloadAction()
    {
    	$document = null;
    	
    	if (null===($file=$this->params('file',null))) {
    		throw new \Exception('file id does not exist');
    	}
    	
    	try {
    		$document = $this->getManagerDms()->loadDocument($file)->getDocument();
    	} catch (\Exception $e) {
    		$content = "file " . $file . " not found";	
    	}
    	
    	if ($document) {
    		$content = $document->getDatas();
    		$headers = $this->getResponse()->getHeaders();
    		$headers->addHeaderLine('Content-type','application/octet-stream');
    		$headers->addHeaderLine("Content-Transfer-Encoding", $document->getEncoding());
    		$headers->addHeaderLine('Content-Length', strlen($content));
    		$name = $document->getName();
    		$headers->addHeaderLine('Content-Disposition', sprintf('filename=%s', ((empty($name)) ? $file . '.' . $document->getFormat() : $name)));
    	}
    	
    	return $this->getResponse()->setContent($content);
    }
    
    public function getTypeAction()
    {
        $content = null;

        if (null!==($file=$this->params('file',null))) {
            $m_document = $this->getManagerDms()->loadDocumentInfo($file)->getDocument();
            if ($m_document) {
                $type = $m_document->getType();
                $content = (empty($type) ? $m_document->getFormat() : $type);
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function getNameAction()
    {
        $content = null;

        if (null!==($file=$this->params('file',null))) {
            $m_document = $this->getManagerDms()->loadDocumentInfo($file)->getDocument();
            if ($m_document) {
                $content = $m_document->getName();
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function getDescriptionAction()
    {
        $content = null;

        if (null!==($file=$this->params('file',null))) {
            $m_document = $this->getManagerDms()->loadDocumentInfo($file)->getDocument();
            if ($m_document) {
                $content = $m_document->getDescription();
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
                    $document['data']   = $file;
                    $document['name']   = $file['name'];
                    $document['type']   = $file['type'];
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

        return new JsonModel(array("result" => true));
    }

    /**
     *
     * @return \Dms\Document\Manager
     */
    public function getManagerDms()
    {
        return $this->getServiceLocator()->get('dms.manager');
    }

    /**
     *
     * @return \Dms\Service\DmsService
     */
    public function getServiceDms()
    {
        return $this->getServiceLocator()->get('dms.service');
    }
}
