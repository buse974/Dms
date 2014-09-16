<?php

namespace Dms\Convert;

use Dms\Convert\Exception\ConvertException;

class Convert
{
    protected $data;
    protected $format;
    protected $page;

    /**
     *
     * @param  string               $data
     * @return \Dms\Convert\Convert
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     *
     * @param  string               $format
     * @return \Dms\Convert\Convert
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     *
     * @param  string               $page
     * @return \Dms\Convert\Convert
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Convert datas Format IN > Format OUT
     *
     * @param  string                    $data_in
     * @param  string                    $format_in
     * @param  string                    $format_out
     * @throws ImagickException|Eception
     * @return string|NULL
     */
    public function getConvertData($format)
    {
    	$tmp_work = '/tmp/';
        $datas = null;
        try {
            $im = new \Imagick();
            $im->readimageblob($this->data);
            if (null!==$this->page) {
                $im->setiteratorindex($this->page);
            }
            $im->setImageFormat($format);
            $datas = $im->getimageblob();
        } catch (\ImagickException $e) {
            $page_opt = (null!==$this->page) ? sprintf("-e PageRange=%d-%d",$this->page,$this->page) : '';

           $uniq_name = $tmp_work . uniqid('UNO');
           $actual_file = sprintf('%s.%s',$uniq_name,$this->format);
            try {
                $process = new Process();
                $process->setCmd(sprintf("cat - > %s && unoconv %s -f %s --stdout %s",$actual_file,$page_opt,$format,$actual_file))
                        ->setInput($this->data)
                        ->setTmp('/tmp');
                $datas = $process->run();
            } catch (ConvertException $e) {
                $process = new Process();
                $process->setCmd(sprintf("cat - > %s && unoconv %s -f pdf %s && unoconv -f %s --stdout %s.pdf",$actual_file,$page_opt,$actual_file,$format,$uniq_name))
                        ->setInput($this->data)
                        ->setTmp('/tmp');
                $datas = $process->run();
            }
        }

        return $datas;
    }
}
