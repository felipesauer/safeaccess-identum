import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Mercosul vehicle plate numbers.
 */
export class PlateMercosulValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        let value = this._raw.toUpperCase().trim();
        value = value.replace(/[^A-Z0-9]/g, '');

        if (value.length !== 7) {
            return false;
        }

        return /^[A-Z]{3}[0-9][A-Z][0-9]{2}$/.test(value);
    }
}
