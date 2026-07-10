import type { ReasonCode } from './reason-code.js';

/**
 * Metadata extracted from a valid document, offline, from the number itself.
 *
 * Fixed shape: every field is always present and defaults to `null`, mirroring
 * the PHP `DocumentMeta` value object exactly so that `meta` serializes
 * identically across both runtimes. Only the fields meaningful to a given
 * document carry a value (e.g. `uf` for CPF/IE, `type` for CNS); the rest are
 * `null`. Build instances with {@link documentMeta}.
 */
export interface DocumentMeta {
    /** Federative unit (state), e.g. 'SP' — CPF (fiscal region) and IE. */
    readonly uf: string | null;
    /** Document subtype, e.g. CNS 'definitive'/'provisional'. */
    readonly type: string | null;
    /** Card brand inferred from the BIN (best-effort). */
    readonly brand: string | null;
    /** PIX key type, e.g. 'cpf', 'email', 'phone', 'evp'. */
    readonly keyType: string | null;
    /** CNPJ: whether it is a headquarters (matriz) rather than a branch. */
    readonly isMatriz: boolean | null;
    /** CNPJ: whether the number uses the alphanumeric format. */
    readonly isAlphanumeric: boolean | null;
    /** Plate layout, e.g. 'mercosul' or 'old'. */
    readonly pattern: string | null;
}

/**
 * Builds a {@link DocumentMeta}, filling every unspecified field with `null`.
 * Mirrors the PHP `DocumentMeta` constructor's named-arg defaults, keeping the
 * object shape identical across runtimes.
 */
export function documentMeta(fields: Partial<DocumentMeta>): DocumentMeta {
    return {
        uf: fields.uf ?? null,
        type: fields.type ?? null,
        brand: fields.brand ?? null,
        keyType: fields.keyType ?? null,
        isMatriz: fields.isMatriz ?? null,
        isAlphanumeric: fields.isAlphanumeric ?? null,
        pattern: fields.pattern ?? null,
    };
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
