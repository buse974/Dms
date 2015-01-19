<?php

namespace Dms\Convert;

use Dms\Convert\Exception\ProcessException;

class Process
{
    protected $cmd;
    protected $env;
    protected $error_code;
    protected $error_message;
    protected $tmp;
    protected $output;
    protected $input;
    protected $descriptors = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w"),
                3 => array("pipe", "w"),
        );

    public function setCmd($cmd)
    {
        $this->cmd = $cmd.' ; echo $? >&3';

        return $this;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }

    public function getErrorMessage()
    {
        return $this->error_message;
    }

    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    public function setTmp($tmp)
    {
        $this->tmp = $tmp;

        return $this;
    }

    public function setEnv($env)
    {
        $this->env = $env;

        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function run()
    {
        $this->error_code = null;
        $this->error_message = null;
        $this->output = null;

        $process = proc_open($this->cmd, $this->descriptors, $pipes, $this->tmp, $this->env);

        if (is_resource($process)) {
            fwrite($pipes[0], $this->input);
            fclose($pipes[0]);
            $this->output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $this->error_code = (int) fgets($pipes[3]);
            fclose($pipes[3]);
            $this->error_message = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($process);
        }

        if ($this->error_code != 0) {
            throw new ProcessException($this->error_message, $this->error_code);
        }

        return $this->output;
    }

    public function setDescriptors(array $descriptors)
    {
        $descriptors[3] = array("pipe", "w");

        $this->descriptors = $descriptors;

        return $this;
    }
}
