<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

use SafeAccess\Identum\Exceptions\ValidationException;

/**
 * Contract for document validators.
 *
 * Defines the public API that all document validators must implement,
 * including validation, failure handling, and input access.
 *
 * @api
 *
 * @see AbstractValidatableDocument Default implementation base.
 */
interface ValidatableDocument
{
    /**
     * Validates the current value.
     *
     * @return bool True when valid, false otherwise.
     */
    public function validate(): bool;

    /**
     * Validates or throws a ValidationException with a concise reason.
     *
     * @return true
     *
     * @throws ValidationException
     */
    public function validateOrFail(): true;

    /**
     * Sets values that should be considered invalid by default.
     *
     * @param list<string> $values
     * @return static
     */
    public function blacklist(array $values): static;

    /**
     * Sets values that should be considered valid by default.
     *
     * @param list<string> $values
     * @return static
     */
    public function whitelist(array $values): static;

    /**
     * Returns the raw (as provided) input value.
     *
     * @return string
     */
    public function raw(): string;
}
