import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { documentMeta, type DocumentMeta } from '../../contracts/validation-result.js';
import { CPFValidation } from '../cpf/cpf-validation.js';
import { CNPJValidation } from '../cnpj/cnpj-validation.js';

type PixKeyType = 'cpf' | 'cnpj' | 'email' | 'phone' | 'evp';

const EVP = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
// E.164: '+' then up to 15 digits total (leading digit is non-zero).
const PHONE = /^\+[1-9][0-9]\d{1,13}$/;
// Pragmatic e-mail shape (W3C-ish); DICT caps the length at 77.
const EMAIL = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

/**
 * Validates PIX keys (BACEN DICT), a composite of the five key types:
 * CPF, CNPJ, e-mail, phone (E.164) and the random key (EVP, a UUID).
 *
 * Validation is format + checksum only (CPF/CNPJ reuse their validators); it
 * does NOT check whether the key is actually registered in the DICT — that is
 * an online lookup. Detected type is exposed as `meta.keyType`.
 */
export class PixValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'pix';
    }

    /** PIX keys are not digit-only; keep the trimmed value verbatim. */
    protected sanitize(value: string): string {
        return value.trim();
    }

    protected doValidate(): ReasonCode | null {
        const value = this.sanitize(this._raw);

        switch (PixValidation.detectType(value)) {
            case 'cpf':
                return new CPFValidation(value).validate().valid ? null : ReasonCode.BadCheckDigit;
            case 'cnpj':
                return new CNPJValidation(value).validate().valid ? null : ReasonCode.BadCheckDigit;
            case 'email':
            case 'phone':
            case 'evp':
                return null;
            default:
                return ReasonCode.InvalidFormat;
        }
    }

    /**
     * Classifies the key by shape. Order matters: '+' and '@' are unambiguous;
     * a UUID is checked before digit-only so it never falls through; then the
     * digit-only CPF (11) / CNPJ (14) lengths.
     */
    private static detectType(value: string): PixKeyType | null {
        if (value === '') return null;
        if (value[0] === '+') return PHONE.test(value) ? 'phone' : null;
        if (value.includes('@')) return value.length <= 77 && EMAIL.test(value) ? 'email' : null;
        if (EVP.test(value)) return 'evp';
        if (/^\d{11}$/.test(value)) return 'cpf';
        if (/^\d{14}$/.test(value)) return 'cnpj';
        return null;
    }

    /** The detected key type (null when the key is invalid — not reached on the valid path). */
    protected extractMeta(normalized: string): DocumentMeta {
        return documentMeta({ keyType: PixValidation.detectType(normalized) });
    }
}
