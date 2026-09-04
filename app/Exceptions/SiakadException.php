<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Base exception class for SIAKAD application
 */
abstract class SiakadException extends Exception
{
    protected string $errorCode;
    protected int $httpStatus = Response::HTTP_BAD_REQUEST;
    protected array $context = [];

    public function __construct(string $message = '', array $context = [], ?Exception $previous = null)
    {
        $this->context = $context;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get the error code for logging/API responses
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get HTTP status code for API responses
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Get additional context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception for HTTP responses
     */
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $this->errorCode,
                    'message' => $this->getMessage(),
                ],
            ], $this->httpStatus);
        }

        return redirect()->back()
            ->with('error', $this->getMessage())
            ->withInput();
    }

    /**
     * Report the exception for logging
     */
    public function report(): void
    {
        // Log with context for debugging
        \Log::warning("[{$this->errorCode}] {$this->getMessage()}", $this->context);
    }
}
