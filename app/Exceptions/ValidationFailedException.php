<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationFailedException extends ApiException
{
    protected $statusCode = 422;
    protected $errorCode = 'VALIDATION_FAILED';
    protected $userMessage = 'The provided data is invalid';
    
    protected array $errors = [];

    public function __construct(array $errors = [], string $message = '')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'status' => $this->statusCode
            ],
            'validation_errors' => $this->errors
        ], $this->statusCode);
    }
}