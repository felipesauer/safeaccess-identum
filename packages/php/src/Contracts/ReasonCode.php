<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

/**
 * Machine-readable reason a validation failed.
 *
 * Stable, language-independent codes (snake_case string values) intended for
 * programmatic handling and as i18n message keys — never localize the code
 * itself. The string values are identical in the TypeScript package and are
 * asserted by the cross-language parity suite.
 *
 * Reasons follow a deterministic precedence when more than one could apply:
 * {@see self::InvalidFormat} → {@see self::WrongLength} → {@see self::BadCheckDigit}
 * → semantic ({@see self::UnknownUf}) → lists ({@see self::KnownInvalid},
 * {@see self::Denied}). Validators report the first that applies in that order.
 *
 * @api
 */
enum ReasonCode: string
{
    /** Characters or shape are wrong (non-digits, bad separators, wrong charset). */
    case InvalidFormat = 'invalid_format';

    /** Right character class, wrong number of characters. */
    case WrongLength = 'wrong_length';

    /** Shape and length are fine, but the check digit(s) do not verify. */
    case BadCheckDigit = 'bad_check_digit';

    /** The referenced UF (state) is not a valid Brazilian federative unit. */
    case UnknownUf = 'unknown_uf';

    /** A reserved sequence that is never issued (e.g., 111.111.111-11). */
    case KnownInvalid = 'known_invalid';

    /** The value matched a user-provided deny list. */
    case Denied = 'denied';
}
