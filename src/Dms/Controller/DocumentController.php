<?php

namespace Dms\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Dms\Model\Dms;
use Zend\View\Model\JsonModel;
use Dms\Service\DmsService as Sp;

class DocumentController extends AbstractActionController
{
    public function getAction()
    {
        $document = null;
        if (null!==($file=$this->params('file',null))) {
            try {
                $document = $this->getManagerDms()->getDocumentById($file);
            } catch (\Exception $e) {}
            try {
                if (!$document && strpos($this->params('file'), '-')!==false) {
                    $t = explode('-', $this->params('file'));
                    $document = $this->getManagerDms()->getDocumentById($t[0]);
                    if ($document) {
                        $this->getManagerDms()->resizeDocument($t[1]);
                        $document = $this->getManagerDms()->getDocument();
                    }
               }
           } catch (\Exception $e) {
                   echo $e->getMessage();
                   exit();
           }

           if ($document) {
               $content = $document->getDatas();
                $headers = $this->getResponse()->getHeaders();

                if ($document->getType()!=null) {
                    $headers->addHeaderLine('Content-type',$document->getType());
                }
                $headers->addHeaderLine("Content-Transfer-Encoding", $document->getEncoding());
                $headers->addHeaderLine('Access-Control-Allow-Origin','*');
                $headers->addHeaderLine('Content-Length', strlen($content));
                $headers->addHeaderLine('Content-Disposition', 'filename=\'' . $document->getName() . '\'');
                $headers->addHeaderLine('Access-Control-Allow-Credentials','true');
           } else {
               $content = "file " . $file . " not found";
           }
        }

        return $this->getResponse()->setContent($content);

    }

    public function getTypeAction()
    {
        $content = null;

        if (null!==($file=$this->params('file',null))) {
            $doc = $this->getManagerDms()->getInfoDocument($file);
            if ($doc) {
                $content = $doc->getType();
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function getNameAction()
    {
        $content = null;
        if (null!==($file=$this->params('file',null))) {
            $doc = $this->getManagerDms()->getInfoDocument($file);
            if ($doc) {
                $content = $doc->getName();
            }
        }

        return $this->getResponse()->setContent($content);
    }

    public function getDescriptionAction()
    {
        $content = null;
        if (null!==($file=$this->params('file',null))) {
            $doc = $this->getManagerDms()->getInfoDocument($file);
            if ($doc) {
                $content = $doc->getDescription();
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
                    $document['support'] = 'file_multi_part';
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
