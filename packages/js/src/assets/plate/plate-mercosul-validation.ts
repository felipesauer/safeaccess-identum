import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Mercosul vehicle plate numbers.
 */
export class PlateMercosulValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'plate';
    }

    /**
     * Plates are alphanumeric, so they cannot strip to digits only.
     * Uppercases and removes every non-alphanumeric character (dashes, spaces).
     */
    protected sanitize(value: string): string {
        return value.toUpperCase().trim().replace(/[^A-Z0-9]/g, '');
    }

    protected doValidate(): boolean {
        const value = this.sanitize(this._raw);

        // Mercosul plate format: LLLNLNN (3 letters + 1 digit + 1 letter + 2 digits = 7 total characters)
        // Example: BRA1A23
        if (value.length !== 7) {
            return false;
        }

        // Validate pattern: exactly 3 letters, then digit, then letter, then 2 digits
        return /^[A-Z]{3}[0-9][A-Z][0-9]{2}$/.test(value);
    }
}
