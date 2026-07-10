import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { documentMeta, type DocumentMeta } from '../../contracts/validation-result.js';

/** Book-type digit (position 15) → certificate kind. */
const BOOK_TYPES: Record<string, string> = {
    '1': 'birth', // Livro A
    '2': 'marriage', // Livro B
    '3': 'marriage', // Livro B-Auxiliar
    '4': 'death', // Livro C
    '5': 'stillbirth', // Livro C-Auxiliar (natimorto)
    '7': 'other', // Livro E
};

/**
 * Validates Brazilian civil registry certificate matrículas (nationwide 32-digit
 * model — CNJ Provimento 63/2017, consolidated by Provimento 149/2023).
 *
 * Layout (32 digits): CNS serventia(6) · acervo(2) · RCPN(2) · year(4) ·
 * book type(1) · book(5) · sheet(3) · term(7) · D1(1) · D2(1).
 *
 * Check digits use Mod-11 with a ×10 step: each DV is (weightedSum × 10) % 11,
 * where a remainder of 10 maps to 1. Not to be confused with the 20-digit
 * judicial process number (Mod-97) or the real-estate CNM (Mod-97).
 */
export class CertidaoValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'certidao';
    }

    protected doValidate(): ReasonCode | null {
        const digits = this.sanitize(this._raw);

        // Matrícula must have exactly 32 digits.
        if (digits.length !== 32) {
            return ReasonCode.WrongLength;
        }

        const d1 = CertidaoValidation.checkDigit(digits.slice(0, 30), 31);
        const d2 = CertidaoValidation.checkDigit(digits.slice(0, 31), 32);

        if (digits[30] !== String(d1) || digits[31] !== String(d2)) {
            return ReasonCode.BadCheckDigit;
        }

        return null;
    }

    /**
     * Mod-11 ×10 check digit: weighted sum of `digits` with descending weights
     * from `startWeight` down to 2, then (sum × 10) % 11; a remainder of 10 → 1.
     */
    private static checkDigit(digits: string, startWeight: number): number {
        let sum = 0;
        for (let i = 0, w = startWeight; i < digits.length; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const dv = (sum * 10) % 11;
        return dv === 10 ? 1 : dv;
    }

    /** Certificate kind derived from the book-type digit (position 15). */
    protected extractMeta(normalized: string): DocumentMeta {
        return documentMeta({ type: BOOK_TYPES[normalized[14]] ?? null });
    }
}
