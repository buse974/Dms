<?php

namespace Dms;

use Dms\Storage\Storage;
use Dms\Document\Manager;
use Dms\Service\DmsService;
use Dms\Coding\Url\Url;
use Dms\Resize\Resize;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\RequestInterface;
use Dms\Document\NoFileException;
use Dms\Document\Document;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes'       => $this->getRoutes(),
            'dms-conf' => [
                'size_allowed' => [
                    ['width' => 300, 'height' => 200],
                    ['width' => 300, 'height' => 300],
                    ['width' => 150, 'height' => 100],
                    ['width' => 80, 'height' => 80],
                    ['width' => 300],
                ],
                'check_size_allowed' => false,
                'default_path' => 'upload/',
                'adapter' => 'http-adapter',
                'convert' => [
                    'tmp' => '/tmp/',
                ],
                'headers'=> [
                    'Access-Control-Allow-Origin'=>'http://local.wow.in',
                    'Access-Control-Allow-Credentials'=>'true'
                ],
                'storage' => [
                    'name' => 's3',
                    'bucket' => 'NAME-BUCKET',
                    'options' => [
                        'version' => 'latest',
                        'region' => '',
                        'credentials' => [
                            'key'    => '',
                            'secret' => '',
                        ],
                    ],
                ],
                /*  'storage' => [
                 'name' => 'gs',
                 'bucket' => 'NAME-BUCKET',
                 'options' => [
                 'projectId' => *****
                 ]
                 ]*/
                
            ],
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies() : array
    {
        return [
            'aliases' => [
                'dms.service' => \Dms\Service\DmsService::class,
                'dms.manager' => \Dms\Document\Manager::class,
                'Base64Coding' => 'BaseCoding',
                'UrlCoding' => \Dms\Coding\Url\Url::class,
                'Resize' => \Dms\Resize\Resize::class,
                'Storage' => \Dms\Storage\Storage::class,
            ],
            'invokables' => [
                'BaseCoding' => \Dms\Coding\Base\Base::class,
                'GzipCoding' => \Dms\Coding\Gzip\Gzip::class,
                'ZlibCoding' => \Dms\Coding\Zlib\Zlib::class,
            ],
            'abstract_factories' => [
                \Dms\ServiceFactory\CodingFactory::class,
            ],
            'factories' => [
                \Dms\Coding\Url\Url::class => function ($container) {
                    $config = $container->get('Config');
                    $url = new Url();
                    $url->setAdapter($config[$config['dms-conf']['adapter']]);
                    
                    return $url;
                },
                \Dms\Resize\Resize::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new Resize([
                        'allow' => $config['size_allowed'],
                        'active' => $config['check_size_allowed'],
                    ]);
                },
                \Dms\Storage\Storage::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new Storage([
                        'path' => $config['default_path'],
                        'storage' => $config['storage'],
                    ]);
                },
                \Dms\Document\Manager::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new Manager($config, $container);
                },
                \Dms\Service\DmsService::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new DmsService($container->get(\Dms\Document\Manager::class), $config);
                },
                
                
                'Action\fileviewAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
                        try {
                            $container->get(\Dms\Service\DmsService::class)->get($request->getAttribute('file'));
                        } catch (NoFileException $e) {
                            return new JsonResponse(['error' => $e->getMessage()]);
                        }
                    };
                },
                'Action\initsessionAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        return new JsonResponse(['result' => true], 200, $container->get('config')['dms-conf']['headers']);
                    };
                },
                'Action\fileprogressAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        return new JsonResponse(Sp::progressAction($request->getAttribute('uploadUID')), 200, $container->get('config')['dms-conf']['headers']);
                    };
                },
                'Action\filesaveAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {

                    	if (session_status() == PHP_SESSION_NONE) {
                    		session_start();
                    	}
                    	
                    	$ret = [];
                    	$request = $this->getRequest();
                    	$files = $request->getFiles()->toArray();
                    	foreach ($files as $name_file => $file) {
                    		if (isset($file['name'])) {
                    			$file = [$file];
                    		}
                    		foreach ($file as $f) {
                    			$document['support'] = Document::SUPPORT_FILE_MULTI_PART_STR;
                    			$document['coding'] = 'binary';
                    			$document['data'] = [$name_file => $f];
                    			$document['name'] = $f['name'];
                    			$document['type'] = $f['type'];
                    			$document['weight'] = $f['size'];
                    			
                    			$doc = $this->dms()->getService()->add($document);
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
                    	}
                    	
                    	return new JsonResponse((string) $ret, 200, $container->get('config')['dms-conf']['headers']);
                    };
                },
                'Action\filedownloadAction' => function ($container) {
	                return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
	                	$content = null;
	                	try {
	                		$document = $container->get(\Dms\Service\DmsService::class)->getDocument($request->getAttribute('file'));
	                		
	                		$content = $document->getDatas();
	                		$headers = $this->getResponse()->getHeaders();
	                		$headers->addHeaderLine('Content-type', 'application/octet-stream');
	                		$headers->addHeaderLine('Content-Transfer-Encoding', $document->getEncoding());
	                		$headers->addHeaderLine('Content-Length', strlen($content));
	                		$name = $document->getName();
	                		$headers->addHeaderLine('Content-Disposition', sprintf('filename=%s', ((empty($name)) ? $file.'.'.$document->getFormat() : $name)));
	                	} catch (NoFileException $e) {
	                		$content = $e->getMessage();
	                	}
	                	
	                	return $this->getResponse()->setContent($content);
	                };
                },
                'Action\filedescriptionAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
                    	$content = null;
                    	try {
                    		$content = $container->get(\Dms\Service\DmsService::class)->getInfo($request->getAttribute('file'), 'description');
                    	} catch (NoFileException $e) {
                    		$content = $e->getMessage();
                    	}
                    	
                    	return new JsonResponse((string) $content);
                    };
                },
                'Action\fileformatAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
                    	$content = null;
                    	try {
                    		$content = $container->get(\Dms\Service\DmsService::class)->getInfo($request->getAttribute('file'), 'format');
                    	} catch (NoFileException $e) {
                    		$content = $e->getMessage();
                    	}
                    	
                    	return new JsonResponse((string) $content);
                    };
                },
                'Action\filenamneAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
                    	$content = null;
                    	try {
                    		$content = $container->get(\Dms\Service\DmsService::class)->getInfo($request->getAttribute('file'), 'namne');
                    	} catch (NoFileException $e) {
                    		$content = $e->getMessage();
                    	}
                    	
                    	return new JsonResponse((string) $content);
                    };
                },
                'Action\filetypeAction' => function ($container) {
                    return function (RequestInterface $request, DelegateInterface $delegate) use ($container) {
                        $content = null;
                        try {
                        	$content = $container->get(\Dms\Service\DmsService::class)->getInfo($request->getAttribute('file'), 'type');
                        } catch (NoFileException $e) {
                            $content = $e->getMessage();
                        }
                        
                        return new JsonResponse((string) $content);
                    };
                },
                
            ],
        ];
    }
    
    /**
     * Get routes
     * 
     * @return array
     */
    public function getRoutes() : array
    {
        return [[
                'name'            => 'fileview',
                'path'            => '/data', ///data/:file
                'middleware'      => 'Action\fileviewAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'initsession',
                'path'            => '/initsession', // /initsession[/]
                'middleware'      => 'Action\initsessionAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'fileprogress',
                'path'            => '/progress', // /progress[/]
                'middleware'      => 'Action\fileprogressAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'filesave',
                'path'            => '/save', // /save[/]
                'middleware'      => 'Action\filesaveAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'filedescription',
                'path'            => '/description', // /description/:file
                'middleware'      => 'Action\filedescriptionAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'fileformat',
                'path'            => '/format/:file', // /format/:file
                'middleware'      => 'Action\fileformatAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'filenamne',
                'path'            => '/name/:file', // /name/:file
                'middleware'      => 'Action\filenamneAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'filetype',
                'path'            => '/type/:file', // /type/:file
                'middleware'      => 'Action\filetypeAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'filedownload',
                'path'            => '/download/:file', // /download/:file
                'middleware'      => 'Action\filedownloadAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],
        ];
        
        
        
        /*
         *         'router' => array(
            'routes' => array(
                'fileview' => array(
                    'type' => 'Segment',
                    
                ),
                'filedownload' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/download/:file',
                        'defaults' => array(
                                'controller' => 'ged_document',
                                'action' => 'getDownload',
                        ),
                    ),
                ),
                'filetype' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/type/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'getType',
                                ),
                        ),
                ),
                'filenamne' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/name/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'getName',
                                ),
                        ),
                ),
                'fileformat' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/format/:file',
                        'defaults' => array(
                            'controller' => 'ged_document',
                            'action' => 'getFormat',
                        ),
                    ),
                ),
                'filedescription' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/description/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'getDescription',
                                ),
                        ),
                ),
                'filesave' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/save[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'save',
                                    ),
                            ),
                ),
                'fileprogress' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/progress[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'progress',
                                    ),
                            ),
                ),
                'initsession' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/initsession[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'initSession',
                                    ),
                            ),
                ),
            ),
        ),
         
         */
    }
}
