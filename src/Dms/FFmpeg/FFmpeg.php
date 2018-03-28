<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * FFmpeg.php
 */
namespace Dms\FFmpeg;

use FFMpeg\FFMpeg as BFF;
use FFMpeg\Coordinate as BFFC;
use FFMpeg\Media\Video;

/**
 * Class FFmpeg.
 */
class FFmpeg
{
    /**
     * File.
     *
     * @var string
     */
    protected $file;

    /**
     * FFMpeg Object.
     *
     * @var \FFMpeg\FFMpeg
     */
    private $ffmpeg;

    /**
     * Video FFMpeg Object.
     *
     * @var Video
     */
    private $video;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->ffmpeg = BFF::create();
    }

    /**
     * Set File.
     *
     * @param string $file
     *
     * @return \Dms\FFmpeg\FFmpeg
     */
    public function setFile($file)
    {
        $this->file = $file;
        $this->video = $this->ffmpeg->open($this->file);

        return $this;
    }

    /**
     * Get Picture.
     *
     * @param int $time
     *
     * @return string
     */
    public function getPicture($time = 30)
    {
        $duration = $this->video->getStreams()->first()->get('duration');

        $this->video->frame(BFFC\TimeCode::fromSeconds($duration * ($time / 100)))->save('/tmp/picture.jpg');

        return file_get_contents('/tmp/picture.jpg');
    }

    /**
     * Get Size.
     *
     * @return string
     */
    public function getSize()
    {
        $stream = $this->video->getStreams()->first();
        $dim = null;
        if ($stream->isVideo()) {
            $dim = $stream->getDimensions()->getWidth().'x'.$stream->getDimensions()->getHeight();
        }

        return $dim;
    }

    /**
     * Get Mine Type.
     *
     * @return string
     */
    public function getTypeMine()
    {
        return 'image/jpeg';
    }

    /**
     * Get Fomat.
     *
     * @return string
     */
    public function getFormat()
    {
        return 'jpg';
    }
}
