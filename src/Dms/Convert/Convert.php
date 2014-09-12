<?php

namespace Dms\Convert;

use Dms\Convert\Exception\ConvertException;

class Convert
{
    protected $data;
    protected $format;

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
            $im->setImageFormat($format);
            $datas = $im->getimageblob();
        } catch (\ImagickException $e) {
            try {
                $process = new Process();
                $process->setCmd(sprintf("cat - > /tmp/tmp.%s && unoconv -f %s --stdout /tmp/tmp.%s",$this->format,$format,$this->format))
                        ->setInput($this->data);
                $datas = $process->run();
            } catch (ConvertException $e) {
                $process = new Process();
                $process->setCmd(sprintf("cat - > /tmp/tmp.%s && unoconv -e PageRange=1-1 -f pdf /tmp/tmp.%s && unoconv -e PageRange=1-1 -f %s --stdout /tmp/tmp.pdf",$this->format,$this->format,$format))
                        ->setInput($this->data);
                $datas = $process->run();
            }
        }

        return $datas;
    }
}
