<?php

namespace App\Exceptions;

class ResourceNotFoundException extends ApiException
{
    protected $statusCode = 404;
    protected $errorCode = 'RESOURCE_NOT_FOUND';
    protected $userMessage = 'The requested resource was not found';
}