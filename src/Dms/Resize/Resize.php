<?php

namespace Dms\Resize;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Resize implements ServiceManagerAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    protected $data;
    protected $formateur;
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = new ResizeOption($options);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * resize data use library GD
     *
     * @param  unknown    $size
     * @throws \Exception
     * @return string
     */
    public function getResizeData($size)
    {
        if (empty($this->data) && empty($size)) {
            throw new \Exception('No data binary or size define');
        }

        $arr_size = array();
        if (is_string($size)) {
            $size = explode('x', $size);
            if ( isset($size[0]) && !empty($size[0])) {
                $arr_size['width'] = $size[0];
            }
            if ( isset($size[1]) && !empty($size[1])) {
                $arr_size['height'] = $size[1];
            }
            $size = $arr_size;
        }

        $size_allowed = $this->options->getAllow();
        if (!empty($size_allowed) && !in_array($size, $size_allowed)) {
                throw new \Exception('size conversion denied',3299);
        }

        $img = @imagecreatefromstring($this->data);
        if (!$img) {
            throw new \Exception('Data is not in a recognized format');
        }

        $oriX = imagesx($img);
        $oriY = imagesy($img);

        if ( (!isset($size['width']) || $oriX < $size['width']) && ( !isset($size['height']) || $oriY < $size['height'])) {
            throw new \Exception('Size is not valid');
        }

        $rapportY = (isset($size['height'])) ? $oriY / $size['height'] : 0;
        $rapportX = (isset($size['width'])) ? $oriX / $size['width'] : 0;
        $raportMax = ($rapportY > $rapportX ? $rapportY : $rapportX);
        $optimalWidth = $oriX / $raportMax;
        $optimalHeight = $oriY / $raportMax;
        $imgResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagefill($imgResized,0,0,imagecolorallocate($imgResized,255,255,255));
        imagecopyresampled($imgResized, $img, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $oriX, $oriY);

        ob_start();
        imagejpeg($imgResized, null, 85);
        $imageFileContents = ob_get_contents();
        ob_end_clean();

    return $imageFileContents;

    }

    /**
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getTypeMine()
    {
        return 'image/jpeg';
    }

    public function getFormat()
    {
        return 'jpeg';
    }
}
