<?php
namespace App\Exception;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct(string $message="User Not Found", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
