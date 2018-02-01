<?php

namespace Dms;

use Dms\Storage\Storage;
use Dms\Document\Manager;
use Dms\Service\DmsService;
use Dms\Coding\Url\Url;
use Dms\Resize\Resize;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Diactoros\Response\JsonResponse;
use Dms\Document\NoFileException;
use Dms\Document\Document;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

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
                ],
               /* 'storage' => [
                    'name' => 's3',
                    'bucket' => 'NAME-BUCKET',
                    'bucket_upload' => 'NAME-BUCKET',
                    'options' => [
                        'version' => 'latest',
                        'region' => '',
                        'credentials' => [
                            'key'    => '',
                            'secret' => '',
                        ],
                    ],
                ],*/
                /*  'storage' => [
                 'name' => 'gs',
                 'bucket' => 'NAME-BUCKET',
                 'bucket_upload' => 'NAME-BUCKET',
                 'credentials_file' => 'tagncar-c26f3232cbb7.json',
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
                    return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
                        try {
                            $container->get(\Dms\Service\DmsService::class)->get($request->getAttribute('file'));
                        } catch (NoFileException $e) {
                            return new JsonResponse(['error' => $e->getMessage()]);
                        }
                    };
                },
                'Action\initsessionAction' => function ($container) {
                    return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        return new JsonResponse(['result' => true], 200, $container->get('config')['dms-conf']['headers']);
                    };
                },
                'Action\fileprogressAction' => function ($container) {
                    return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        return new JsonResponse(Sp::progressAction($request->getAttribute('uploadUID')), 200, $container->get('config')['dms-conf']['headers']);
                    };
                },
                'Action\filesaveAction' => function ($container) {
                	return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {

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

                    		$doc = $container->get(\Dms\Service\DmsService::class)->add($document);
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
                    	
                    	return new JsonResponse($ret, 200, $container->get('config')['dms-conf']['headers']);
                    };
                },
                'Action\filedownloadAction' => function ($container) {
                	return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
	                	$content = null;
	                	try {
	                		$document = $container->get(\Dms\Service\DmsService::class)->getDocument($request->getAttribute('file'));
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
	                	
	                	return new TextResponse($content, 200, $headers);
	                };
                },
                'Action\filedescriptionAction' => function ($container) {
                	return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
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
                	return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
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
                	return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
                    	$content = null;
                    	try {
                    		$content = $container->get(\Dms\Service\DmsService::class)->getInfo($request->getAttribute('file'), 'name');
                    	} catch (NoFileException $e) {
                    		$content = $e->getMessage();
                    	}
                    	
                    	return new JsonResponse((string) $content);
                    };
                },
                'Action\filetypeAction' => function ($container) {
                	return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
                        $content = null;
                        try {
                        	$content = $container->get(\Dms\Service\DmsService::class)->getInfo($request->getAttribute('file'), 'type');
                        } catch (NoFileException $e) {
                            $content = $e->getMessage();
                        }
                        
                        return new JsonResponse((string) $content);
                    };
                },
                'Action\filecopyAction' => function ($container) {
                	return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($container) {
                		if (session_status() == PHP_SESSION_NONE) {
                			session_start();
                		}
                		
                		$document = [];
                		$document['support'] = Document::SUPPORT_FILE_BUCKET_STR;
                		$document['coding'] = 'binary';
                		$document['data'] =  $request->getParsedBody()['object'];
                		$document['name'] = $request->getParsedBody()['name'];

                		$doc = $container->get(\Dms\Service\DmsService::class)->add($document);
 
                		return new JsonResponse(['id'=>$doc], 200, $container->get('config')['dms-conf']['headers']);
                			
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
        return [
        	[
                'name'            => 'fileview',
                'path'            => '/data/{file}',
                'middleware'      => 'Action\fileviewAction',
                'allowed_methods' => ['GET', 'OPTIONS'],
        	],[
        	    'name'            => 'fileformat',
        	    'path'            => '/format/{file}',
        	    'middleware'      => 'Action\fileformatAction',
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filetype',
        	    'path'            => '/type/{file}',
        	    'middleware'      => 'Action\filetypeAction',
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filesave',
        	    'path'            => '/save[/]',
        	    'middleware'      => 'Action\filesaveAction',
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filenamne',
        	    'path'            => '/name/{file}',
        	    'middleware'      => 'Action\filenamneAction',
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filedownload',
        	    'path'            => '/download/{file}',
        	    'middleware'      => 'Action\filedownloadAction',
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filecopy',
        	    'path'            => '/copy[/]',
        	    'middleware'      => 'Action\filecopyAction',
        	    'allowed_methods' => ['POST', 'OPTIONS'],
        	],
            
            
            
            
            [
                'name'            => 'initsession',
                'path'            => '/initsession[/]', 
                'middleware'      => 'Action\initsessionAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'fileprogress',
                'path'            => '/progress[/]', 
                'middleware'      => 'Action\fileprogressAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'filedescription',
                'path'            => '/description/{file}',
                'middleware'      => 'Action\filedescriptionAction',
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],
        ];
    }
}
