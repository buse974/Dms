<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * Process
 */
namespace Dms\Convert;

use Dms\Convert\Exception\ProcessException;

/**
 * Class Process.
 */
class Process
{
    /**
     * Command.
     *
     * @var string
     */
    protected $cmd;

    /**
     * Environement.
     *
     * @var array
     */
    protected $env;

    /**
     * Code Error.
     *
     * @var int
     */
    protected $error_code;

    /**
     * Message Error.
     *
     * @var string
     */
    protected $error_message;

    /**
     * Folder Tmp.
     *
     * @var string
     */
    protected $tmp;

    /**
     * Output Command.
     *
     * @var string
     */
    protected $output;

    /**
     * Input Parm Command.
     *
     * @var string
     */
    protected $input;

    /**
     * Descriptors.
     *
     * @var array
     */
    protected $descriptors = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
                3 => array('pipe', 'w'),
        );

    /**
     * Set Command.
     *
     * @param string $cmd
     *
     * @return \Dms\Convert\Process
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd.' ; echo $? >&3';

        return $this;
    }

    /**
     * Get Error Code.
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * Get Error Message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * Set Input.
     *
     * @param string $input
     *
     * @return \Dms\Convert\Process
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Set Folder Tmp.
     *
     * @param string $tmp
     *
     * @return \Dms\Convert\Process
     */
    public function setTmp($tmp)
    {
        $this->tmp = $tmp;

        return $this;
    }

    /**
     * Set Env.
     *
     * @param string $env
     *
     * @return \Dms\Convert\Process
     */
    public function setEnv($env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Get Output.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Run Command.
     *
     * @throws ProcessException
     *
     * @return null|string
     */
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

    /**
     * Set Descriptor.
     *
     * @param array $descriptors
     *
     * @return \Dms\Convert\Process
     */
    public function setDescriptors(array $descriptors)
    {
        $descriptors[3] = array('pipe', 'w');

        $this->descriptors = $descriptors;

        return $this;
    }
}
