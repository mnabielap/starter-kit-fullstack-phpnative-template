<?php

namespace App\Utils;

class ApiError extends \Exception
{
    public $statusCode;
    public $isOperational;

    public function __construct(int $statusCode, string $message, bool $isOperational = true, string $stack = '')
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->isOperational = $isOperational;
    }
}