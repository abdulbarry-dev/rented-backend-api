<?php

namespace App\Exceptions;

class ConflictException extends ApiException
{
    protected $statusCode = 409;
    protected $errorCode = 'CONFLICT';
    protected $userMessage = 'The request conflicts with the current state';
}