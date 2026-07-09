<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

/**
 * Rich outcome of a validation.
 *
 * Returned by {@see ValidatableDocument::validate()}. On success, {@see $valid}
 * is true, {@see $reason} is null, and {@see $meta} may carry extracted
 * metadata. On failure, {@see $valid} is false and {@see $reason} holds the
 * machine-readable cause. {@see $normalized} always reflects the sanitized
 * canonical form of the input, valid or not.
 *
 * Mirrors the TypeScript `ValidationResult` object for parity.
 *
 * @api
 */
final readonly class ValidationResult
{
    public function __construct(
        public bool $valid,
        public ?ReasonCode $reason,
        public string $normalized,
        public ?DocumentMeta $meta = null,
    ) {
    }

    /** Convenience constructor for a valid result. */
    public static function valid(string $normalized, ?DocumentMeta $meta = null): self
    {
        return new self(true, null, $normalized, $meta);
    }

    /** Convenience constructor for an invalid result. */
    public static function invalid(ReasonCode $reason, string $normalized): self
    {
        return new self(false, $reason, $normalized, null);
    }
}
