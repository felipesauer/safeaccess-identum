<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\Plate;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

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
     * Plates are alphanumeric, so they cannot strip to digits only.
     * Uppercases and removes every non-alphanumeric character (dashes, spaces).
     */
    protected function sanitize(string $value): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($value))) ?? '';
    }

    protected function doValidate(): bool
    {
        $value = $this->sanitize($this->raw());

        // Mercosul plate format: LLLNLNN (3 letters + 1 digit + 1 letter + 2 digits = 7 total characters)
        // Example: BRA1A23
        if (strlen($value) !== 7) {
            return false;
        }

        // Validate pattern: exactly 3 letters, then digit, then letter, then 2 digits
        return (bool) preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $value);
    }
}
