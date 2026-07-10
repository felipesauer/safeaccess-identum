<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\Plate;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\DocumentMeta;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Mercosul vehicle plate numbers.
 *
 * @api
 */
final class PlateMercosulValidation extends AbstractValidatableDocument
{
    protected function documentName(): string
    {
        return 'plate';
    }

    /**
     * Generates a valid Mercosul plate (LLLNLNN, format-only).
     *
     * @param bool $formatted Mercosul plates have no separator; kept for API symmetry.
     */
    public static function generate(bool $formatted = false): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $letter = static fn (): string => $letters[random_int(0, 25)];
        $digit = static fn (): string => (string) random_int(0, 9);

        // Pattern LLLNLNN
        return $letter() . $letter() . $letter() . $digit() . $letter() . $digit() . $digit();
    }

    /**
     * Plates are alphanumeric, so they cannot strip to digits only.
     * Uppercases and removes every non-alphanumeric character (dashes, spaces).
     */
    protected function sanitize(string $value): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($value))) ?? '';
    }

    protected function doValidate(): ?ReasonCode
    {
        $value = $this->sanitize($this->raw());

        // Mercosul plate format: LLLNLNN (3 letters + 1 digit + 1 letter + 2 digits = 7 total characters)
        // Example: BRA1A23
        if (strlen($value) !== 7) {
            return ReasonCode::WrongLength;
        }

        // Validate pattern: exactly 3 letters, then digit, then letter, then 2 digits
        if (preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $value) !== 1) {
            return ReasonCode::InvalidFormat;
        }

        return null;
    }

    /** Layout of a valid plate. This validator only accepts the Mercosul layout. */
    protected function extractMeta(string $normalized): DocumentMeta
    {
        return new DocumentMeta(pattern: 'mercosul');
    }
}
