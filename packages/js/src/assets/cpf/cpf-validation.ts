import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { documentMeta, type DocumentMeta } from '../../contracts/validation-result.js';
import { randomDigits } from '../../contracts/random.js';

/**
 * Fiscal region by the 9th digit (index 8). Each digit maps to a group of
 * states, not a single UF, so the value is the region's state list.
 */
const FISCAL_REGIONS: Record<number, string> = {
    0: 'RS',
    1: 'DF-GO-MS-MT-TO',
    2: 'AC-AM-AP-PA-RO-RR',
    3: 'CE-MA-PI',
    4: 'AL-PB-PE-RN',
    5: 'BA-SE',
    6: 'MG',
    7: 'ES-RJ',
    8: 'SP',
    9: 'PR-SC',
};

/**
 * Validates Brazilian CPF (Cadastro de Pessoas Físicas) numbers.
 *
 * Applies Mod11 check-digit algorithm with two verification digits.
 */
export class CPFValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'cpf';
    }

    /**
     * Generates a valid CPF.
     *
     * @param formatted When true, returns the masked form (000.000.000-00).
     * @returns A number that always passes {@link validate}.
     */
    static generate(formatted = false): string {
        // 9 random base digits, avoiding the all-same-digit sequence (reserved as invalid).
        let base: string;
        do {
            base = randomDigits(9);
        } while (/^(\d)\1{8}$/.test(base));

        const dv1 = CPFValidation.checkDigit(base, 10);
        const dv2 = CPFValidation.checkDigit(base + dv1, 11);
        const value = base + dv1 + dv2;

        return formatted ? new CPFValidation(value).format() : value;
    }

    /**
     * Mod-11 check digit over `digits` with descending weights starting at
     * `startWeight`. Remainder < 2 yields 0 (the CPF convention shared with doValidate).
     */
    private static checkDigit(digits: string, startWeight: number): number {
        let sum = 0;
        for (let i = 0, w = startWeight; i < digits.length; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const rest = sum % 11;
        return rest < 2 ? 0 : 11 - rest;
    }

    protected doValidate(): ReasonCode | null {
        // Strip all non-digit characters to get a clean numeric string
        const digits = this.sanitize(this._raw);

        // CPF must have exactly 11 digits
        if (digits.length !== 11) {
            return ReasonCode.WrongLength;
        }

        // Guard: Receita Federal (Brazilian tax authority) reserves all 11-same-digit sequences
        // (e.g., 000...000, 111...111) as invalid forever — no valid CPF exists with all same digits.
        if (/^(\d)\1{10}$/.test(digits)) {
            return ReasonCode.KnownInvalid;
        }

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of first 9 digits (weights 10 down to 2) modulo 11.
        // If remainder < 2, DV1 = 0; otherwise DV1 = 11 - remainder.
        let sum = 0;
        for (let i = 0, w = 10; i < 9; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const rest1 = sum % 11;
        const dv1 = rest1 < 2 ? 0 : 11 - rest1;

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted sum of first 9 digits PLUS DV1 (weights 11 down to 2) modulo 11.
        // If remainder < 2, DV2 = 0; otherwise DV2 = 11 - remainder.
        // Note: the 10th digit slot (index 9) in the weighted sum is DV1, followed by original digits 10–11 (indices 8–9).
        sum = 0;
        for (let i = 0, w = 11; i < 10; i++, w--) {
            sum += Number(digits[i]) * w;
        }
        const rest2 = sum % 11;
        const dv2 = rest2 < 2 ? 0 : 11 - rest2;

        // Final verification: check if the computed DV1/DV2 match the digits at positions 9 and 10
        if (digits[9] !== String(dv1) || digits[10] !== String(dv2)) {
            return ReasonCode.BadCheckDigit;
        }

        return null;
    }

    /** Fiscal region (group of states) inferred from the 9th digit. */
    protected extractMeta(normalized: string): DocumentMeta {
        return documentMeta({ uf: FISCAL_REGIONS[Number(normalized[8])] ?? null });
    }

    /** Canonical CPF mask: 000.000.000-00. */
    protected mask(stripped: string): string {
        return stripped.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
    }
}
