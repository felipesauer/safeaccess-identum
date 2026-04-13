<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

use SafeAccess\Identum\Exceptions\ValidationException;

/**
 * Base implementation for document validators.
 *
 * Provides the template method pattern: subclasses implement {@see doValidate()}
 * with format-specific validation logic, while this class handles blacklist/whitelist
 * filtering and the validate/validateOrFail lifecycle.
 *
 * @internal Extend this class to create new document validators.
 *
 * @see ValidatableDocument       Contract this class implements.
 * @see AbstractValidatableDocumentRules Extended base for rule-dispatched validators.
 */
abstract class AbstractValidatableDocument implements ValidatableDocument
{
    /**
     * The raw input value, as provided by the caller.
     *
     * @var string
     */
    protected string $raw;

    /**
     * List of values that should always be considered invalid.
     *
     * @var list<string>
     */
    protected array $blacklist = [];

    /**
     * List of values that should always be considered valid.
     *
     * @var list<string>
     */
    protected array $whitelist = [];

    /**
     * Create a new document validator with the given raw input.
     *
     * @param string $value Raw input (may include separators, case, or extra characters).
     */
    public function __construct(string $value)
    {
        $this->raw = $value;
    }

    /**
     * Returns the raw, unmodified input value.
     */
    public function raw(): string
    {
        return $this->raw;
    }

    /**
     * Defines values that should always be treated as invalid.
     *
     * @param list<string> $values
     * @return static
     */
    public function blacklist(array $values): static
    {
        $this->blacklist = $values;
        return $this;
    }

    /**
     * Defines values that should always be treated as valid.
     *
     * @param list<string> $values
     * @return static
     */
    public function whitelist(array $values): static
    {
        $this->whitelist = $values;
        return $this;
    }

    /**
     * Performs validation against the current input value.
     *
     * @return bool True if valid, false otherwise.
     */
    public function validate(): bool
    {
        if ($this->isWhitelisted($this->raw)) {
            return true;
        }

        if ($this->isBlacklisted($this->raw)) {
            return false;
        }

        return $this->doValidate();
    }

    /**
     * Performs validation and throws if the value is invalid.
     *
     * @return true Always returns true when valid.
     *
     * @throws ValidationException If validation fails.
     */
    public function validateOrFail(): true
    {
        if (!$this->validate()) {
            throw new ValidationException('input invalid');
        }

        return true;
    }

    /**
     * Subclasses must implement the actual validation logic
     *
     * @return bool True if valid, false otherwise.
     */
    abstract protected function doValidate(): bool;

    /**
     * Checks if the given value is in the blacklist.
     *
     * @param string $value
     * @return boolean
     */
    protected function isBlacklisted(string $value): bool
    {
        return in_array($value, $this->blacklist, true);
    }

    /**
     * Checks if the given value is in the whitelist.
     *
     * @param string $value
     * @return boolean
     */
    protected function isWhitelisted(string $value): bool
    {
        return in_array($value, $this->whitelist, true);
    }
}
