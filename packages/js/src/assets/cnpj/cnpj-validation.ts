import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { documentMeta, type DocumentMeta } from '../../contracts/validation-result.js';
import { randomDigits } from '../../contracts/random.js';

/**
 * Validates Brazilian CNPJ (Cadastro Nacional da Pessoa Jurídica) numbers.
 *
 * Supports both numeric and alphanumeric CNPJ formats.
 */
export class CNPJValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'cnpj';
    }

    /**
     * Generates a valid (numeric) CNPJ.
     *
     * @param formatted When true, returns the masked form (00.000.000/0000-00).
     */
    static generate(formatted = false): string {
        const w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        const w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        let base: string;
        do {
            base = randomDigits(12);
        } while (/^(\d)\1{11}$/.test(base));

        let sum = 0;
        for (let i = 0; i < 12; i++) sum += Number(base[i]) * w1[i];
        let rest = sum % 11;
        const dv1 = rest < 2 ? 0 : 11 - rest;

        sum = 0;
        for (let i = 0; i < 12; i++) sum += Number(base[i]) * w2[i];
        sum += dv1 * w2[12];
        rest = sum % 11;
        const dv2 = rest < 2 ? 0 : 11 - rest;

        const value = base + dv1 + dv2;
        return formatted ? new CNPJValidation(value).format() : value;
    }

    /**
     * CNPJ keeps letters (alphanumeric format), so it cannot strip to digits only.
     * Uppercases and removes only formatting separators; other characters are
     * preserved so they are caught as invalid during validation.
     */
    protected sanitize(value: string): string {
        return value.toUpperCase().replace(/[\s.\-/]/g, '');
    }

    protected doValidate(): ReasonCode | null {
        const txt = this.sanitize(this._raw);

        // Guard: CNPJ must be exactly 14 characters long
        if (txt.length !== 14) {
            return ReasonCode.WrongLength;
        }

        // Guard: only [A-Z0-9] are allowed; check digits (positions 12–13) must be numeric
        if (!/^[A-Z0-9]{12}[0-9]{2}$/.test(txt)) {
            return ReasonCode.InvalidFormat;
        }

        // Guard: if purely numeric, reject the all-same-digit pattern
        if (/^\d{14}$/.test(txt) && /^(\d)\1{13}$/.test(txt)) {
            return ReasonCode.KnownInvalid;
        }

        const body12 = txt.slice(0, 12);
        const dvIn1 = Number(txt[12]);
        const dvIn2 = Number(txt[13]);

        // Character → integer value mapper: charCodeAt(0) - 48.
        // Digits '0'–'9' map to 0–9; letters 'A'–'Z' map to 17–42.
        // The InvalidFormat guard above already ensured every body char is [A-Z0-9].
        const val = (ch: string): number => ch.charCodeAt(0) - 48;

        // Weights for DV1 and DV2 calculations
        const w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        const w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // ===== First Verification Digit (DV1) =====
        // Sum of (character_value × weight) for the first 12 positions, then modulo 11.
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += val(body12[i]) * w1[i];
        }
        const rest1 = sum % 11;
        const dv1 = rest1 < 2 ? 0 : 11 - rest1;

        // ===== Second Verification Digit (DV2) =====
        // Sum of (character_value × weight) for the first 12 positions PLUS (DV1 × w2[12]).
        sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += val(body12[i]) * w2[i];
        }
        sum += dv1 * w2[12];
        const rest2 = sum % 11;
        const dv2 = rest2 < 2 ? 0 : 11 - rest2;

        // Final verification: check if computed DV1/DV2 match the input check digits
        return dvIn1 === dv1 && dvIn2 === dv2 ? null : ReasonCode.BadCheckDigit;
    }

    /**
     * CNPJ metadata: whether it is a headquarters (branch marker '0001' before the
     * check digits) and whether it uses the alphanumeric format (any letter present).
     */
    protected extractMeta(normalized: string): DocumentMeta {
        return documentMeta({
            isMatriz: normalized.slice(8, 12) === '0001',
            isAlphanumeric: /[A-Z]/.test(normalized),
        });
    }

    /**
     * Canonical CNPJ mask: XX.XXX.XXX/XXXX-YY. Alphanumeric-aware — the first 12
     * positions may be [A-Z0-9] and only the last 2 (check digits) are numeric.
     */
    protected mask(stripped: string): string {
        return stripped.replace(
            /^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{4})(\d{2})$/,
            '$1.$2.$3/$4-$5',
        );
    }
}
