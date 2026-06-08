import { ValidationException } from '../exceptions/validation-exception.js';

/**
 * Contract for document validators.
 *
 * Defines the public API that all document validators must implement,
 * including validation, failure handling, and input access.
 */
export interface ValidatableDocument {
    /** Validates the current value. */
    validate(): boolean;
    /**
     * Validates or throws a ValidationException with a concise reason.
     *
     * @throws {@link ValidationException} If validation fails.
     */
    validateOrFail(): true;
    /** Sets values that should be considered invalid by default. */
    blacklist(values: string[]): this;
    /** Sets values that should be considered valid by default. */
    whitelist(values: string[]): this;
    /** Returns the raw (as provided) input value. */
    raw(): string;
}

/**
 * Base implementation for document validators.
 *
 * Provides the template method pattern: subclasses implement {@link doValidate}
 * with format-specific validation logic, while this class handles blacklist/whitelist
 * filtering and the validate/validateOrFail lifecycle.
 *
 * @internal Extend this class to create new document validators.
 *
 * @see {@link ValidatableDocument} Contract this class implements.
 */
export abstract class AbstractValidatableDocument implements ValidatableDocument {
    protected readonly _raw: string;
    protected _blacklist: string[] = [];
    protected _whitelist: string[] = [];

    constructor(value: string) {
        this._raw = value;
    }

    raw(): string {
        return this._raw;
    }

    blacklist(values: string[]): this {
        this._blacklist = values;
        return this;
    }

    whitelist(values: string[]): this {
        this._whitelist = values;
        return this;
    }

    validate(): boolean {
        if (this.isWhitelisted(this._raw)) {
            return true;
        }

        if (this.isBlacklisted(this._raw)) {
            return false;
        }

        return this.doValidate();
    }

    validateOrFail(): true {
        if (!this.validate()) {
            throw new ValidationException(`${this.documentName()}: input invalid`);
        }

        return true;
    }

    protected abstract doValidate(): boolean;

    /**
     * Short identifier of the document type, used in error messages
     * (e.g., "cpf", "cnpj"). Must match the PHP counterpart.
     */
    protected abstract documentName(): string;

    /**
     * Normalizes a value for comparison and validation.
     *
     * Default: strips every non-digit character. Validators whose format keeps
     * letters (e.g., alphanumeric CNPJ, Mercosul plate) override this.
     */
    protected sanitize(value: string): string {
        return value.replace(/\D+/g, '');
    }

    /** Whitelist/blacklist comparisons are format-agnostic: both sides are sanitized first. */
    protected isBlacklisted(value: string): boolean {
        const target = this.sanitize(value);
        return this._blacklist.some((entry) => this.sanitize(entry) === target);
    }

    protected isWhitelisted(value: string): boolean {
        const target = this.sanitize(value);
        return this._whitelist.some((entry) => this.sanitize(entry) === target);
    }
}
