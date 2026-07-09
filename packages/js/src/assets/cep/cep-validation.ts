import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { randomDigits } from '../../contracts/random.js';

/**
 * Validates Brazilian CEP (Código de Endereçamento Postal) numbers.
 */
export class CEPValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'cep';
    }

    /**
     * Generates a valid CEP (8 digits; format-only, no check digit).
     *
     * @param formatted When true, returns the masked form (00000-000).
     */
    static generate(formatted = false): string {
        const value = randomDigits(8);
        return formatted ? new CEPValidation(value).format() : value;
    }

    protected doValidate(): ReasonCode | null {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this.sanitize(this._raw);

        // CEP (postal code) must have exactly 8 digits.
        // NOTE: This validator performs format validation only — range and locality rules
        // are the responsibility of the consuming application, as new ranges may be assigned
        // by the Brazilian postal service (ECT) after this library's release.
        return digits.length === 8 ? null : ReasonCode.WrongLength;
    }

    /** Canonical CEP mask: 00000-000. */
    protected mask(stripped: string): string {
        return stripped.replace(/^(\d{5})(\d{3})$/, '$1-$2');
    }
}
