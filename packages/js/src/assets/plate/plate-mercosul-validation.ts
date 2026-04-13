import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Mercosul vehicle plate numbers.
 */
export class PlateMercosulValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        // Normalize to uppercase and strip separators (dashes, spaces)
        let value = this._raw.toUpperCase().trim();
        // Remove any non-alphanumeric characters (e.g., dashes, spaces in LLLNLNN format)
        value = value.replace(/[^A-Z0-9]/g, '');

        // Mercosul plate format: LLLNLNN (3 letters + 1 digit + 1 letter + 2 digits = 7 total characters)
        // Example: BRA1A23
        if (value.length !== 7) {
            return false;
        }

        // Validate pattern: exactly 3 letters, then digit, then letter, then 2 digits
        return /^[A-Z]{3}[0-9][A-Z][0-9]{2}$/.test(value);
    }
}
