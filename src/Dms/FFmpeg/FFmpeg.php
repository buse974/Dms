<?php

namespace Dms\FFmpeg;

use \FFMpeg\FFMpeg as BFF;
use \FFMpeg\Coordinate as BFFC;
use FFMpeg\Filters\Frame\FrameFilters;

class FFmpeg
{
    protected $file;
    private $ffmpeg;
    private $video;
    
    public function __construct()
    {
        $this->ffmpeg = BFF::create();
    }
    
    public function setFile($file) 
    {
        $this->file = $file;
        $this->video = $this->ffmpeg->open($this->file);
        
        return $this;
    }
    
    public function getPicture($time = 30)
    {
       $duration = $this->video->getStreams()->first()->get('duration');

       $this->video->frame(BFFC\TimeCode::fromSeconds($duration*($time/100)))->save('/tmp/picture.jpg');
       
       return file_get_contents('/tmp/picture.jpg');
    }
    
    public function getSize()
    {
        return $this->video->getStreams()->first()->getDimensions()->getWidth().'x'.$this->video->getStreams()->first()->getDimensions()->getHeight();
    }
    
    public function getTypeMine()
    {
        return 'image/jpeg';
    }
    
    public function getFormat()
    {
        return 'jpg';
    }
}
