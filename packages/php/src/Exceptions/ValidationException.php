<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Exceptions;

use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Root exception for all validation errors. Catch this to handle any
 * failure coming out of the library without importing every subclass.
 *
 * Carries structured context — the document type, the machine-readable
 * {@see ReasonCode}, and the normalized input — so callers can branch
 * programmatically instead of parsing the message string.
 *
 * @api
 *
 * @see InvalidStateRuleException
 */
class ValidationException extends \RuntimeException
{
    public function __construct(
        public readonly string $document,
        public readonly ReasonCode $reason,
        public readonly string $normalized,
    ) {
        parent::__construct("{$document}: {$reason->value}");
    }
}
