import type { ReasonCode } from './reason-code.js';

/**
 * Metadata extracted from a valid document, offline, from the number itself.
 *
 * Every field is optional and only populated by validators for which it is
 * meaningful (e.g. `uf` for CPF/IE, `type` for CNS). A validator that has
 * nothing to extract returns `null` for `ValidationResult.meta` instead of an
 * all-empty object. Mirrors the PHP `DocumentMeta` value object.
 */
export interface DocumentMeta {
    /** Federative unit (state), e.g. 'SP' — CPF (fiscal region) and IE. */
    readonly uf?: string;
    /** Document subtype, e.g. CNS 'definitive'/'provisional'. */
    readonly type?: string;
    /** Card brand inferred from the BIN (best-effort). */
    readonly brand?: string;
    /** PIX key type, e.g. 'cpf', 'email', 'phone', 'evp'. */
    readonly keyType?: string;
    /** CNPJ: whether it is a headquarters (matriz) rather than a branch. */
    readonly isMatriz?: boolean;
    /** CNPJ: whether the number uses the alphanumeric format. */
    readonly isAlphanumeric?: boolean;
    /** Plate layout, e.g. 'mercosul' or 'old'. */
    readonly pattern?: string;
}

/**
 * Rich outcome of a validation.
 *
 * Returned by `ValidatableDocument.validate()`. On success `valid` is true,
 * `reason` is null, and `meta` may carry extracted metadata. On failure `valid`
 * is false and `reason` holds the machine-readable cause. `normalized` always
 * reflects the sanitized canonical form of the input, valid or not.
 *
 * Mirrors the PHP `ValidationResult` object for parity.
 */
export interface ValidationResult {
    readonly valid: boolean;
    readonly reason: ReasonCode | null;
    readonly normalized: string;
    readonly meta: DocumentMeta | null;
}
