import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CEP (Código de Endereçamento Postal) numbers.
 */
export class CEPValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this._raw.replace(/\D+/g, '');

        // CEP (postal code) must have exactly 8 digits.
        // NOTE: This validator performs format validation only — range and locality rules
        // are the responsibility of the consuming application, as new ranges may be assigned
        // by the Brazilian postal service (ECT) after this library's release.
        return digits.length === 8;
    }
}
