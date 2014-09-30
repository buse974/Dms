<?php

namespace Dms\Convert;

use Dms\Convert\Exception\ConvertException;

class Convert
{
    protected $data;
    protected $format;
    protected $tmp='';
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

    public function setTmp($tmp)
    {
        $this->tmp = $tmp;

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
               $uniq_name = $this->tmp . uniqid('UNO');
               $actual_file = sprintf('%s.%s',$uniq_name,($this->format) ?: 'tmp');
            try {
                $process = new Process();
                $process->setCmd(sprintf("cat - > %s && unoconv %s -f %s --stdout %s",$actual_file,$page_opt,$format,$actual_file))
                        ->setInput($this->data);
                $datas = $process->run();
            } catch (ConvertException $e) {
                $process = new Process();
                $process->setCmd(sprintf("cat - > %s && unoconv %s -f pdf %s && unoconv -f %s --stdout %s.pdf",$actual_file,$page_opt,$actual_file,$format,$uniq_name))
                        ->setInput($this->data);
                $datas = $process->run();
            }

            $process = new Process();
            $process->setCmd(sprintf("rm -f %s.*",$uniq_name))->run();
        }

        return $datas;
    }
}
