<?php

namespace Dms\Exception;

class ForbiddenDocumentException extends \Exception
{
    protected $code = 403;
    protected $message = 'Error in cURL request: The requested URL returned error: 403 Forbidden';

    public function __construct($message = null, $code = null, $previous = null)
    {
        if ($message !== null) {
            $this->setMessage($message);
        }
    }

    public function setMessage($message)
    {
        $this->message = $this->message.' => '.$message;

        return $this;
    }
}
