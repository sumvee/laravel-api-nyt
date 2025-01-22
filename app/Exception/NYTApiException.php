<?php

namespace App\Exception;

use Exception;
use Throwable;

class NYTApiException extends Exception
{
    public function __construct($message = 'NYT API Error', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


}
