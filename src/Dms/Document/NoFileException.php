<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * NoFileException
 */
namespace Dms\Document;

/**
 * Class NoFileException.
 */
class NoFileException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->message = sprintf('Param is not id: %s', $token);
    }
}
