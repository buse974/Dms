<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * Forbidden DocumentException
 */
namespace Dms\Coding\Url\Exception;

/**
 * Class ForbiddenDocumentException.
 */
class ForbiddenDocumentException extends \Exception
{
    /**
     * Code Error.
     *
     * @var int
     */
    protected $code = 403;

    /**
     * Message Error.
     *
     * @var string
     */
    protected $message = 'Error in cURL request: The requested URL returned error: 403 Forbidden';

    /**
     * Constructor.
     *
     * @param string $message
     * @param int    $code
     * @param array  $previous
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($message !== null) {
            $this->setMessage($message);
        }
    }

    /**
     * Set Message.
     *
     * @param string $message
     *
     * @return \Dms\Coding\Url\Exception\ForbiddenDocumentException
     */
    public function setMessage($message)
    {
        $this->message = $this->message.' => '.$message;

        return $this;
    }
}
