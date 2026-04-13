<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Exceptions;

/**
 * Thrown when an invalid or unsupported state rule is requested.
 *
 * Raised when the provided state code does not match any registered
 * IE (Inscrição Estadual) validation rule.
 *
 * @api
 *
 * @see ValidationException                                    Parent exception class.
 * @see \SafeAccess\Identum\Assets\IE\IEValidation            Class that throws this exception.
 * @see \SafeAccess\Identum\Assets\IE\StateEnum                Enum of valid state codes.
 */
class InvalidStateRuleException extends ValidationException
{
    /**
     * Create a new invalid state rule exception.
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
