<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

use SafeAccess\Identum\Exceptions\ValidationException;

/**
 * Base for document validators.
 *
 * Subclasses implement {@see doValidate()} with document-specific logic;
 * this class owns the blacklist/whitelist checks and the validate/validateOrFail flow.
 *
 * @internal
 *
 * @see ValidatableDocument
 * @see AbstractValidatableDocumentRules
 */
abstract class AbstractValidatableDocument implements ValidatableDocument
{
    /** @var string */
    protected string $raw;

    /** @var list<string> */
    protected array $blacklist = [];

    /** @var list<string> */
    protected array $whitelist = [];

    public function __construct(string $value)
    {
        $this->raw = $value;
    }

    /** Returns the raw input as originally provided. */
    public function raw(): string
    {
        return $this->raw;
    }

    /**
     * @param list<string> $values
     * @return static
     */
    public function blacklist(array $values): static
    {
        $this->blacklist = $values;
        return $this;
    }

    /**
     * @param list<string> $values
     * @return static
     */
    public function whitelist(array $values): static
    {
        $this->whitelist = $values;
        return $this;
    }

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
     * @throws ValidationException
     */
    public function validateOrFail(): true
    {
        if (!$this->validate()) {
            throw new ValidationException($this->documentName() . ': input invalid');
        }

        return true;
    }

    abstract protected function doValidate(): bool;

    /**
     * Short identifier of the document type, used in error messages
     * (e.g., "cpf", "cnpj"). Must match the JS counterpart.
     */
    abstract protected function documentName(): string;

    /**
     * Normalizes a value for comparison and validation.
     *
     * Default: strips every non-digit character. Validators whose format keeps
     * letters (e.g., alphanumeric CNPJ, Mercosul plate) override this.
     */
    protected function sanitize(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /** Whitelist/blacklist comparisons are format-agnostic: both sides are sanitized first. */
    protected function isBlacklisted(string $value): bool
    {
        return in_array($this->sanitize($value), array_map($this->sanitize(...), $this->blacklist), true);
    }

    protected function isWhitelisted(string $value): bool
    {
        return in_array($this->sanitize($value), array_map($this->sanitize(...), $this->whitelist), true);
    }
}
