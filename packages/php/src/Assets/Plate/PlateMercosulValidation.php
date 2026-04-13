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
    protected function doValidate(): bool
    {
        $value = strtoupper(trim($this->raw()));
        // aceita com ou sem hífen/espaço
        $value = preg_replace('/[^A-Z0-9]/', '', $value) ?? '';

        // Formato Mercosul: LLLNLNN (ex.: BRA1A23)
        if (strlen($value) !== 7) {
            return false;
        }

        // L(0-2) L(0-2) L(0-2) N(3) L(4) N(5) N(6)
        return (bool) preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $value);
    }
}
