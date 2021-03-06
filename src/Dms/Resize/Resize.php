<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * Resize.php
 */
namespace Dms\Resize;

/**
 * Class Resize.
 */
class Resize
{
    /**
     * Data Document.
     *
     * @var string
     */
    protected $data;

    /**
     * Type File.
     *
     * @var string
     */
    protected $format = 'jpg';

    /**
     * Option Resize.
     *
     * @var ResizeOption
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = new ResizeOption($options);
    }

    /**
     * Set Data Binary.
     *
     * @param string $data
     *
     * @return \Dms\Resize\Resize
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set Format.
     *
     * @param string $format
     *
     * @return \Dms\Resize\Resize
     */
    public function setFormat($format)
    {
        if (!empty($format)) {
            $this->format = $format;
        }

        return $this;
    }

    /**
     * resize data use library GD.
     *
     * @param string $size
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getResizeData($size)
    {
        if (empty($this->data) && empty($size)) {
            throw new \Exception('No data binary or size define');
        }

        $min = true;
        $arr_size = array();
        if (is_string($size)) {
            if (strpos($size, 'x') !== false) {
                $size = explode('x', $size);
            } elseif (strpos($size, 'm') !== false) {
                $size = explode('m', $size);
                $min = false;
            } else {
                $size = array($size);
            }

            if (isset($size[0]) && !empty($size[0])) {
                $arr_size['width'] = intval($size[0]);
            }
            if (isset($size[1]) && !empty($size[1])) {
                $arr_size['height'] = intval($size[1]);
            }
            $size = $arr_size;
        }

        $size_allowed = $this->options->getAllow();
        if ($this->options->getActive() && !empty($size_allowed) && !in_array($size, $size_allowed)) {
            throw new \Exception('size conversion denied', 3299);
        }

        $img = @imagecreatefromstring($this->data);
        if (!$img) {
            throw new \Exception('Data is not in a recognized format');
        }

        $oriX = imagesx($img);
        $oriY = imagesy($img);

        if((!isset($size['width']) && !isset($size['height'])) || $oriY < $size['height'] || $oriX < $size['width']) {
            return $this->data;
        }

        $rapportY = (isset($size['height'])) ? $oriY / $size['height'] : 0;
        $rapportX = (isset($size['width'])) ? $oriX / $size['width'] : 0;
        
        if ($min === false) {
            $raportMax = ($rapportY < $rapportX ? $rapportY : $rapportX);
        } else {
            $raportMax = ($rapportY > $rapportX ? $rapportY : $rapportX);
        }

        $optimalWidth = $oriX / $raportMax;
        $optimalHeight = $oriY / $raportMax;
        $imgResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagefill($imgResized, 0, 0, imagecolorallocate($imgResized, 255, 255, 255));
        imagecopyresampled($imgResized, $img, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $oriX, $oriY);

        $q = null;
        switch ($this->format) {
            case 'gif':
                $fn = 'imagegif';
                break;
            case 'png':
                $fn = 'imagepng';
                $q = 0;
                break;
            case 'wbmp':
                $fn = 'imagewbmp';
                break;
            case 'jpg':
            case 'jpeg':
            default:
                $fn = 'imagejpeg';
                $q = 85;
        }

        ob_start();
        $fn($imgResized, null, $q);
        $imageFileContents = ob_get_contents();
        ob_end_clean();

        return $imageFileContents;
    }

    /**
     * Check if is Compatible.
     *
     * @param string $ext
     *
     * @return bool
     */
    public static function isCompatible($ext)
    {
        switch ($ext) {
            case 'gif':
                $t = IMG_GIF;
                break;
            case 'jpg':
            case 'jpeg':
                $t = IMG_JPG;
                break;
            case 'png':
                $t = IMG_PNG;
                break;
            case 'wbmp':
                $t = IMG_WBMP;
                break;
            case 'xmp':
                $t = IMG_XPM;
                break;
            default:
                return false;
        }

        return imagetypes() & $t;
    }

    /**
     * Get Mine Type.
     *
     * @return string
     */
    public function getTypeMine()
    {
        return 'image/'.$this->format;
    }

    /**
     * Get Format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
}
