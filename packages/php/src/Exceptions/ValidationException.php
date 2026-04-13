<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Exceptions;

/**
 * Base exception for all SafeAccess\Identum errors.
 *
 * Serves as the root of the exception hierarchy, enabling catch-all handling
 * for any error originating from document validation operations.
 *
 * @api
 *
 * @see InvalidStateRuleException Thrown when an invalid state rule is requested.
 */
class ValidationException extends \RuntimeException
{
    /**
     * Create a new validation exception.
     *
     * @param string          $message  Human-readable error description.
     * @param int             $code     Application-specific error code.
     * @param \Throwable|null $previous Previous exception for chaining.
     */
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
