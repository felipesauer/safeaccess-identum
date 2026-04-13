import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CEP (Código de Endereçamento Postal) numbers.
 */
export class CEPValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const digits = this._raw.replace(/\D+/g, '');
        return digits.length === 8;
    }
}
