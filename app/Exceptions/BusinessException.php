<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected ?string $error;

    public function __construct(string $message = '', ?string $error = null, int $code = 0)
    {
        parent::__construct($message, $code);
        $this->error = $error;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
