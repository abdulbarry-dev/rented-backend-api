<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiException extends Exception
{
    protected $statusCode = 500;
    protected $errorCode = 'INTERNAL_ERROR';
    protected $userMessage = 'Something went wrong';

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        $message = $message ?: $this->userMessage;
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'status' => $this->statusCode
            ]
        ];

        // Add debug info in development
        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTraceAsString()
            ];
        }

        return response()->json($response, $this->statusCode);
    }

    public function report(): void
    {
        // Log critical errors only
        if ($this->statusCode >= 500) {
            logger()->error($this->getMessage(), [
                'exception' => get_class($this),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTraceAsString()
            ]);
        }
    }
}