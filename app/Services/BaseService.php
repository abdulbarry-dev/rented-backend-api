<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ResourceNotFoundException;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    /**
     * Find model by ID or throw exception
     */
    protected function findOrFail(string $modelClass, int $id): Model
    {
        $model = $modelClass::find($id);
        
        if (!$model) {
            throw new ResourceNotFoundException("Resource with ID {$id} not found");
        }
        
        return $model;
    }

    /**
     * Handle unique constraint violations
     */
    protected function handleUniqueConstraint(callable $operation, string $field = 'email')
    {
        try {
            return $operation();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') { // Unique constraint violation
                throw new ConflictException("The {$field} is already taken");
            }
            throw $e;
        }
    }

    /**
     * Handle database operations with proper exception handling
     */
    protected function executeQuery(callable $operation, string $errorMessage = 'Database operation failed')
    {
        try {
            return $operation();
        } catch (\Illuminate\Database\QueryException $e) {
            // Check for specific error codes
            if ($e->getCode() === '23000') {
                throw new ConflictException('Resource already exists or conflicts with existing data');
            }
            
            // Log the actual error for debugging
            logger()->error('Database query failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            throw new \Exception($errorMessage);
        }
    }
}