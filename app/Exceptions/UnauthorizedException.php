<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    protected $statusCode = 401;
    protected $errorCode = 'UNAUTHORIZED';
    protected $userMessage = 'Authentication required';
}