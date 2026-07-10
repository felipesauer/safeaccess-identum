<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

use SafeAccess\Identum\Exceptions\ValidationException;

/**
 * Contract for document validators.
 *
 * Defines the public API that all document validators must implement:
 * rich validation, a boolean shortcut, throwing validation, format-agnostic
 * allow/deny lists, and access to the raw input.
 *
 * @api
 *
 * @see AbstractValidatableDocument Default implementation base.
 */
interface ValidatableDocument
{
    /**
     * Validates the current value and returns a rich result.
     *
     * @return ValidationResult Outcome carrying validity, reason, normalized form and metadata.
     */
    public function validate(): ValidationResult;

    /**
     * Boolean shortcut for {@see validate()} — true when valid.
     */
    public function isValid(): bool;

    /**
     * Validates or throws.
     *
     * @throws ValidationException When the value is invalid; carries the structured reason.
     */
    public function validateOrFail(): void;

    /**
     * Force-rejects the given values regardless of checksum (format-agnostic).
     *
     * @param list<string> $values
     * @return static
     */
    public function denyList(array $values): static;

    /**
     * Force-accepts the given values regardless of checksum (format-agnostic).
     *
     * @param list<string> $values
     * @return static
     */
    public function allowList(array $values): static;

    /**
     * Returns the raw (as provided) input value.
     */
    public function raw(): string;

    /**
     * Returns the canonical, unformatted value (all mask characters removed).
     */
    public function strip(): string;

    /**
     * Returns the value with its canonical mask applied (best-effort).
     *
     * Presentation helper — does not validate. If the stripped value does not
     * fit the document's mask, the stripped value is returned unchanged.
     */
    public function format(): string;
}
