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
     * 
     * @var string
     */
    private $path;

    /**
     * Path 
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
}
