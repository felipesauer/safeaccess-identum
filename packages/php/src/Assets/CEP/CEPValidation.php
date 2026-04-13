<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CEP;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian CEP (Código de Endereçamento Postal) numbers.
 *
 * @api
 */
final class CEPValidation extends AbstractValidatableDocument
{
    protected function doValidate(): bool
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // CEP (postal code) must have exactly 8 digits.
        // NOTE: This validator performs format validation only — range and locality rules
        // are the responsibility of the consuming application, as new ranges may be assigned
        // by the Brazilian postal service (ECT) after this library's release.
        return strlen($digits) === 8;
    }
}
