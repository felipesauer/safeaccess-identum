<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CEP;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Brazilian CEP (Código de Endereçamento Postal) numbers.
 *
 * @api
 */
final class CEPValidation extends AbstractValidatableDocument
{
    protected function documentName(): string
    {
        return 'cep';
    }

    /**
     * Generates a valid CEP (8 digits; format-only, no check digit).
     *
     * @param bool $formatted When true, returns the masked form (00000-000).
     */
    public static function generate(bool $formatted = false): string
    {
        $value = '';
        for ($i = 0; $i < 8; $i++) {
            $value .= random_int(0, 9);
        }

        return $formatted ? (new self($value))->format() : $value;
    }

    protected function doValidate(): ?ReasonCode
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = $this->sanitize($this->raw());

        // CEP (postal code) must have exactly 8 digits.
        // NOTE: This validator performs format validation only — range and locality rules
        // are the responsibility of the consuming application, as new ranges may be assigned
        // by the Brazilian postal service (ECT) after this library's release.
        return strlen($digits) === 8 ? null : ReasonCode::WrongLength;
    }

    /** Canonical CEP mask: 00000-000. */
    protected function mask(string $stripped): string
    {
        return preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $stripped) ?? $stripped;
    }
}
