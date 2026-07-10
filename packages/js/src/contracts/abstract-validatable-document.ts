import { ValidationException } from '../exceptions/validation-exception.js';
import type { ReasonCode } from './reason-code.js';
import { ReasonCode as Reason } from './reason-code.js';
import type { DocumentMeta, ValidationResult } from './validation-result.js';

/**
 * Contract for document validators.
 *
 * Defines the public API that all document validators must implement:
 * rich validation, a boolean shortcut, throwing validation, format-agnostic
 * allow/deny lists, and access to the raw input.
 *
 * @see {@link AbstractValidatableDocument} Default implementation base.
 */
export interface ValidatableDocument {
    /** Validates the current value and returns a rich result. */
    validate(): ValidationResult;
    /** Boolean shortcut for {@link validate} — true when valid. */
    isValid(): boolean;
    /**
     * Validates or throws a {@link ValidationException} carrying the reason.
     *
     * @throws {@link ValidationException} If validation fails.
     */
    validateOrFail(): void;
    /** Force-rejects the given values regardless of checksum (format-agnostic). */
    denyList(values: string[]): this;
    /** Force-accepts the given values regardless of checksum (format-agnostic). */
    allowList(values: string[]): this;
    /** Returns the raw (as provided) input value. */
    raw(): string;
    /** Returns the canonical, unformatted value (all mask characters removed). */
    strip(): string;
    /**
     * Returns the value with its canonical mask applied (best-effort).
     *
     * Presentation helper — does not validate. If the stripped value does not
     * fit the document's mask, the stripped value is returned unchanged.
     */
    format(): string;
}

/**
 * Base implementation for document validators.
 *
 * Subclasses implement {@link doValidate} with document-specific logic,
 * returning `null` when valid or the {@link ReasonCode} that applies otherwise.
 * This class owns the allow/deny-list checks, metadata assembly and the
 * validate / isValid / validateOrFail lifecycle.
 *
 * @internal Extend this class to create new document validators.
 *
 * @see {@link ValidatableDocument} Contract this class implements.
 */
export abstract class AbstractValidatableDocument implements ValidatableDocument {
    protected readonly _raw: string;
    protected _denyList: string[] = [];
    protected _allowList: string[] = [];

    constructor(value: string) {
        this._raw = value;
    }

    raw(): string {
        return this._raw;
    }

    /** Returns the canonical, unformatted value (all mask characters removed). */
    strip(): string {
        return this.sanitize(this._raw);
    }

    /**
     * Returns the value with its canonical mask applied (best-effort).
     *
     * Presentation helper — does not validate. Delegates to {@link mask}, which
     * returns the stripped value unchanged when it does not fit the mask.
     */
    format(): string {
        return this.mask(this.sanitize(this._raw));
    }

    denyList(values: string[]): this {
        this._denyList = values;
        return this;
    }

    allowList(values: string[]): this {
        this._allowList = values;
        return this;
    }

    /** @deprecated 2.0 Use {@link denyList}. Removed in 3.0. */
    blacklist(values: string[]): this {
        return this.denyList(values);
    }

    /** @deprecated 2.0 Use {@link allowList}. Removed in 3.0. */
    whitelist(values: string[]): this {
        return this.allowList(values);
    }

    validate(): ValidationResult {
        const normalized = this.sanitize(this._raw);

        // Allow list wins over everything, including the checksum. The value was
        // force-accepted by the caller, not proven to be a well-formed document,
        // so no metadata is extracted (extractMeta() assumes a validated shape).
        if (this.isAllowed(this._raw)) {
            return { valid: true, reason: null, normalized, meta: null };
        }

        if (this.isDenied(this._raw)) {
            return { valid: false, reason: Reason.Denied, normalized, meta: null };
        }

        const reason = this.doValidate();

        if (reason !== null) {
            return { valid: false, reason, normalized, meta: null };
        }

        return { valid: true, reason: null, normalized, meta: this.extractMeta(normalized) };
    }

    isValid(): boolean {
        return this.validate().valid;
    }

    validateOrFail(): void {
        const result = this.validate();

        if (!result.valid) {
            throw new ValidationException(this.documentName(), result.reason as ReasonCode, result.normalized);
        }
    }

    /**
     * Document-specific validation. Returns `null` when valid, otherwise the
     * reason that applies (respecting the {@link ReasonCode} precedence order).
     */
    protected abstract doValidate(): ReasonCode | null;

    /**
     * Short identifier of the document type, used in exceptions
     * (e.g., "cpf", "cnpj"). Must match the PHP counterpart.
     */
    protected abstract documentName(): string;

    /**
     * Extracts metadata from a valid, normalized value. Default: no metadata.
     * Validators that can derive information from the number override this.
     */
    protected extractMeta(_normalized: string): DocumentMeta | null {
        return null;
    }

    /**
     * Normalizes a value for comparison and validation.
     *
     * Default: strips every non-digit character. Validators whose format keeps
     * letters (e.g., alphanumeric CNPJ, Mercosul plate) override this.
     */
    protected sanitize(value: string): string {
        return value.replace(/\D+/g, '');
    }

    /**
     * Applies the document's canonical mask to an already-stripped value.
     *
     * Default: no mask (documents without a canonical display format). Validators
     * with a mask (CPF, CNPJ, CEP, …) override this and must return the input
     * unchanged when it does not fit the mask (best-effort).
     */
    protected mask(stripped: string): string {
        return stripped;
    }

    /** Deny-list comparison is format-agnostic: both sides are sanitized first. */
    protected isDenied(value: string): boolean {
        const target = this.sanitize(value);
        return this._denyList.some((entry) => this.sanitize(entry) === target);
    }

    /** Allow-list comparison is format-agnostic: both sides are sanitized first. */
    protected isAllowed(value: string): boolean {
        const target = this.sanitize(value);
        return this._allowList.some((entry) => this.sanitize(entry) === target);
    }
}
