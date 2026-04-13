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
        if (this._whitelist.includes(this._raw)) {
            return true;
        }

        if (this._blacklist.includes(this._raw)) {
            return false;
        }

        return this.doValidate();
    }

    validateOrFail(): true {
        if (!this.validate()) {
            throw new ValidationException('input invalid');
        }

        return true;
    }

    protected abstract doValidate(): boolean;
}
