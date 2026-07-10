<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

use SafeAccess\Identum\Exceptions\ValidationException;

/**
 * Base for document validators.
 *
 * Subclasses implement {@see doValidate()} with document-specific logic,
 * returning `null` when valid or the {@see ReasonCode} that applies otherwise.
 * This class owns the allow/deny-list checks, metadata assembly and the
 * validate / isValid / validateOrFail flow.
 *
 * @internal
 *
 * @see ValidatableDocument
 * @see AbstractValidatableDocumentRules
 */
abstract class AbstractValidatableDocument implements ValidatableDocument
{
    protected string $raw;

    /** @var list<string> */
    protected array $denyList = [];

    /** @var list<string> */
    protected array $allowList = [];

    public function __construct(string $value)
    {
        $this->raw = $value;
    }

    /** Returns the raw input as originally provided. */
    public function raw(): string
    {
        return $this->raw;
    }

    /** Returns the canonical, unformatted value (all mask characters removed). */
    public function strip(): string
    {
        return $this->sanitize($this->raw);
    }

    /**
     * Returns the value with its canonical mask applied (best-effort).
     *
     * Presentation helper — does not validate. Delegates to {@see mask()}, which
     * returns the stripped value unchanged when it does not fit the mask.
     */
    public function format(): string
    {
        return $this->mask($this->sanitize($this->raw));
    }

    /**
     * @param list<string> $values
     * @return static
     */
    public function denyList(array $values): static
    {
        $this->denyList = $values;
        return $this;
    }

    /**
     * @param list<string> $values
     * @return static
     */
    public function allowList(array $values): static
    {
        $this->allowList = $values;
        return $this;
    }

    /**
     * @deprecated 2.0 Use {@see denyList()}. Removed in 3.0.
     *
     * @param list<string> $values
     * @return static
     */
    public function blacklist(array $values): static
    {
        return $this->denyList($values);
    }

    /**
     * @deprecated 2.0 Use {@see allowList()}. Removed in 3.0.
     *
     * @param list<string> $values
     * @return static
     */
    public function whitelist(array $values): static
    {
        return $this->allowList($values);
    }

    public function validate(): ValidationResult
    {
        $normalized = $this->sanitize($this->raw);

        // Allow list wins over everything, including the checksum. The value was
        // force-accepted by the caller, not proven to be a well-formed document,
        // so no metadata is extracted (extractMeta() assumes a validated shape).
        if ($this->isAllowed($this->raw)) {
            return ValidationResult::valid($normalized, null);
        }

        if ($this->isDenied($this->raw)) {
            return ValidationResult::invalid(ReasonCode::Denied, $normalized);
        }

        $reason = $this->doValidate();

        if ($reason !== null) {
            return ValidationResult::invalid($reason, $normalized);
        }

        // Reaching here means doValidate() passed, so $normalized has a valid shape.
        return ValidationResult::valid($normalized, $this->extractMeta($normalized));
    }

    public function isValid(): bool
    {
        return $this->validate()->valid;
    }

    public function validateOrFail(): void
    {
        $result = $this->validate();

        if (!$result->valid) {
            /** @var ReasonCode $reason */
            $reason = $result->reason;
            throw new ValidationException($this->documentName(), $reason, $result->normalized);
        }
    }

    /**
     * Document-specific validation.
     *
     * @return ReasonCode|null Null when valid, otherwise the reason that applies
     *                         (respecting the {@see ReasonCode} precedence order).
     */
    abstract protected function doValidate(): ?ReasonCode;

    /**
     * Short identifier of the document type, used in exceptions
     * (e.g., "cpf", "cnpj"). Must match the JS counterpart.
     */
    abstract protected function documentName(): string;

    /**
     * Extracts metadata from a valid, normalized value.
     *
     * Default: no metadata. Validators that can derive information from the
     * number itself (UF, brand, key type, …) override this.
     */
    protected function extractMeta(string $normalized): ?DocumentMeta
    {
        return null;
    }

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

    /**
     * Applies the document's canonical mask to an already-stripped value.
     *
     * Default: no mask (documents without a canonical display format). Validators
     * with a mask (CPF, CNPJ, CEP, …) override this and must return the input
     * unchanged when it does not fit the mask (best-effort).
     */
    protected function mask(string $stripped): string
    {
        return $stripped;
    }

    /** Deny-list comparison is format-agnostic: both sides are sanitized first. */
    protected function isDenied(string $value): bool
    {
        return in_array($this->sanitize($value), array_map($this->sanitize(...), $this->denyList), true);
    }

    /** Allow-list comparison is format-agnostic: both sides are sanitized first. */
    protected function isAllowed(string $value): bool
    {
        return in_array($this->sanitize($value), array_map($this->sanitize(...), $this->allowList), true);
    }
}
