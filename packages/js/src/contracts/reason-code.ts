/**
 * Machine-readable reason a validation failed.
 *
 * Stable, language-independent snake_case codes intended for programmatic
 * handling and as i18n message keys — never localize the code itself. The
 * values are identical to the PHP `ReasonCode` enum and are asserted by the
 * cross-language parity suite.
 *
 * Modeled as a union of string literals (not a TS `enum`) to stay tree-shakeable
 * and free of runtime emit, and a `const` object of the same values for callers
 * that prefer a named reference.
 *
 * Reasons follow a deterministic precedence when more than one could apply:
 * `invalid_format` → `wrong_length` → `bad_check_digit` → semantic (`unknown_uf`)
 * → lists (`known_invalid`, `denied`). Validators report the first that applies.
 */
export const ReasonCode = {
    /** Characters or shape are wrong (non-digits, bad separators, wrong charset). */
    InvalidFormat: 'invalid_format',
    /** Right character class, wrong number of characters. */
    WrongLength: 'wrong_length',
    /** Shape and length are fine, but the check digit(s) do not verify. */
    BadCheckDigit: 'bad_check_digit',
    /** The referenced UF (state) is not a valid Brazilian federative unit. */
    UnknownUf: 'unknown_uf',
    /** A reserved sequence that is never issued (e.g., 111.111.111-11). */
    KnownInvalid: 'known_invalid',
    /** The value matched a user-provided deny list. */
    Denied: 'denied',
} as const;

export type ReasonCode = (typeof ReasonCode)[keyof typeof ReasonCode];
