<?php

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    protected $statusCode = 403;
    protected $errorCode = 'FORBIDDEN';
    protected $userMessage = 'You do not have permission to perform this action';
}