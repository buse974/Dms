<?php
/**
 * 
 * github.com/buse974/Dms (https://github.com/buse974/Dms)
 *
 * StorageOption.php
 *
 */
namespace Dms\Storage;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Storage Option
 */
class StorageOption extends AbstractOptions
{
    /**
     * @var string
     */
    private $path;
    
    /**
     * @var array
     */
    private $storage;

    /**
     * Set Path 
     * 
     * @param string $path
     * @return \Dms\Storage\StorageOption
     */
    public function setPath($path)
    {
        if(substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
            
        $this->path = $path;

        return $this;
    }

    /**
     * Get Path
     * 
     * @return string
     */
    public function getPath()
    {
        if (!$this->path) {
            $this->path = array();
        }

        return $this->path;
    }
    
    /**
     * Set Storage
     * 
     * @param array $storage
     * @return \Dms\Storage\StorageOption
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        
        return $this;
    }
    
    /**
     * Get Storage
     * 
     * @return array
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
