import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CNPJ (Cadastro Nacional da Pessoa Jurídica) numbers.
 *
 * Supports both numeric and alphanumeric CNPJ formats.
 */
export class CNPJValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const raw = this._raw.toUpperCase();
        // Strip common formatting separators (dot, dash, slash) and whitespace.
        // Preserve other characters to catch them as invalid during validation.
        const txt = raw.replace(/[\s.\-/]/g, '');

        // Guard: CNPJ must be exactly 14 characters long
        if (txt.length !== 14) {
            return false;
        }

        // Guard: check digits (positions 12–13) must always be numeric digits
        if (!/^\d{2}$/.test(txt.slice(12))) {
            return false;
        }

        // Guard: if purely numeric, reject the all-same-digit pattern
        if (/^\d{14}$/.test(txt) && /^(\d)\1{13}$/.test(txt)) {
            return false;
        }

        const body12 = txt.slice(0, 12);
        const dvIn1 = Number(txt[12]);
        const dvIn2 = Number(txt[13]);

        // Character → integer value mapper: charCodeAt(0) - 48
        // Digits '0'–'9' map to 0–9; letters 'A'–'Z' map to 17–42.
        const val = (ch: string): number => {
            const o = ch.charCodeAt(0);
            if (o >= 48 && o <= 57) return o - 48; // '0'..'9' → 0..9
            if (o >= 65 && o <= 90) return o - 48; // 'A'..'Z' → 17..42
            return -1; // Invalid character
        };

        // Weights for DV1 and DV2 calculations
        const w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        const w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // ===== First Verification Digit (DV1) =====
        // Sum of (character_value × weight) for the first 12 positions, then modulo 11.
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            const v = val(body12[i]);
            if (v < 0) return false; // Invalid character found
            sum += v * w1[i];
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
        return dvIn1 === dv1 && dvIn2 === dv2;
    }
}
