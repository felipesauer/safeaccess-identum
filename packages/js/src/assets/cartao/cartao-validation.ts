import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { documentMeta, type DocumentMeta } from '../../contracts/validation-result.js';

/**
 * Brand detection by BIN, evaluated in order. Elo/Hipercard come before
 * Visa/Mastercard/Amex because their ranges start with 4/5/6 and would
 * otherwise be shadowed. Each entry is [brand, patterns over the digits].
 */
const BRAND_RULES: Array<[string, RegExp[]]> = [
    [
        'elo',
        [
            /^(401178|401179|431274|438935|451416|457393|457631|457632|504175)/,
            /^(627780|636297|636368)/,
            /^(506699|5067[0-6]\d|50677[0-8])/,
            /^(509\d{3})/,
            /^(65003[1-3]|65003[5-9]|6500[45]\d|65005[01])/,
            /^(65040[5-9]|6504[1-3]\d|65048[5-9]|65049\d|6505[0-2]\d|65053[0-8])/,
            /^(65054[1-9]|6505[5-8]\d|65059[0-8]|6507[0-6]\d|65077[0-8]|65081[0-9]|6508[2-8]\d)/,
            /^(65165[2-9]|6516[6-7]\d|65500\d|6550[1-4]\d|65505[0-8])/,
        ],
    ],
    ['hipercard', [/^(384100|384140|384160|606282|637095|637568|637599|637609|637612)/]],
    ['amex', [/^3[47]/]],
    ['visa', [/^4/]],
    ['mastercard', [/^(5[1-5]|222[1-9]|22[3-9]\d|2[3-6]\d{2}|27[01]\d|2720)/]],
];

/**
 * Validates payment card numbers via the Luhn algorithm (Mod-10, ISO/IEC 7812).
 *
 * Validation is integrity-only: it proves the number is well-formed and passes
 * the check digit — it does NOT prove the card exists, is active, or is issued.
 * The `meta.brand` is a best-effort BIN heuristic that can drift as issuers
 * change ranges; do not rely on it for authorization.
 */
export class CartaoValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'cartao';
    }

    protected doValidate(): ReasonCode | null {
        const digits = this.sanitize(this._raw);

        // ISO/IEC 7812 allows 8–19 digits.
        if (digits.length < 8 || digits.length > 19) {
            return ReasonCode.WrongLength;
        }

        // Reject single-digit sequences (e.g. all zeros) — they pass Luhn but are
        // never real PANs, consistent with the other document validators.
        if (/^(\d)\1*$/.test(digits)) {
            return ReasonCode.KnownInvalid;
        }

        return CartaoValidation.luhnValid(digits) ? null : ReasonCode.BadCheckDigit;
    }

    /** Standard Luhn (Mod-10): double every 2nd digit from the right, sum, % 10 == 0. */
    private static luhnValid(digits: string): boolean {
        let sum = 0;
        let double = false;
        for (let i = digits.length - 1; i >= 0; i--) {
            let d = Number(digits[i]);
            if (double) {
                d *= 2;
                if (d > 9) d -= 9;
            }
            sum += d;
            double = !double;
        }
        return sum % 10 === 0;
    }

    /** Best-effort card brand inferred from the BIN. */
    protected extractMeta(normalized: string): DocumentMeta {
        for (const [brand, patterns] of BRAND_RULES) {
            for (const pattern of patterns) {
                if (pattern.test(normalized)) {
                    return documentMeta({ brand });
                }
            }
        }
        return documentMeta({});
    }
}
