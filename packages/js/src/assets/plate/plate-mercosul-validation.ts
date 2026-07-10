import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { documentMeta, type DocumentMeta } from '../../contracts/validation-result.js';
import { randomInt } from '../../contracts/random.js';

/**
 * Validates Mercosul vehicle plate numbers.
 */
export class PlateMercosulValidation extends AbstractValidatableDocument {
    protected documentName(): string {
        return 'plate';
    }

    /**
     * Generates a valid Mercosul plate (LLLNLNN, format-only). No separator, so
     * there is no `formatted` option.
     */
    static generate(): string {
        const letter = (): string => String.fromCharCode(65 + randomInt(0, 25));
        const digit = (): string => String(randomInt(0, 9));
        // Pattern LLLNLNN
        return letter() + letter() + letter() + digit() + letter() + digit() + digit();
    }

    /**
     * Plates are alphanumeric, so they cannot strip to digits only.
     * Uppercases and removes every non-alphanumeric character (dashes, spaces).
     */
    protected sanitize(value: string): string {
        return value.toUpperCase().trim().replace(/[^A-Z0-9]/g, '');
    }

    protected doValidate(): ReasonCode | null {
        const value = this.sanitize(this._raw);

        // Mercosul plate format: LLLNLNN (3 letters + 1 digit + 1 letter + 2 digits = 7 total characters)
        // Example: BRA1A23
        if (value.length !== 7) {
            return ReasonCode.WrongLength;
        }

        // Validate pattern: exactly 3 letters, then digit, then letter, then 2 digits
        if (!/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/.test(value)) {
            return ReasonCode.InvalidFormat;
        }

        return null;
    }

    /** Layout of a valid plate. This validator only accepts the Mercosul layout. */
    protected extractMeta(): DocumentMeta {
        return documentMeta({ pattern: 'mercosul' });
    }
}
