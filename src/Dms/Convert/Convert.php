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
            try {
                $process = new Process();
                $process->setCmd(sprintf("cat - > /tmp/tmp.%s && unoconv %s -f %s --stdout /tmp/tmp.%s",$this->format,$page_opt,$format,$this->format))
                        ->setInput($this->data);
                $datas = $process->run();
            } catch (ConvertException $e) {
                $process = new Process();
                $process->setCmd(sprintf("cat - > /tmp/tmp.%s && unoconv %s -f pdf /tmp/tmp.%s && unoconv -f %s --stdout /tmp/tmp.pdf",$this->format,$page_opt,$this->format,$format))
                        ->setInput($this->data);
                $datas = $process->run();
            }
        }

        return $datas;
    }
}
