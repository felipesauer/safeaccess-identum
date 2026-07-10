<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\Pix;

use SafeAccess\Identum\Assets\CNPJ\CNPJValidation;
use SafeAccess\Identum\Assets\CPF\CPFValidation;
use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\DocumentMeta;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates PIX keys (BACEN DICT), a composite of the five key types:
 * CPF, CNPJ, e-mail, phone (E.164) and the random key (EVP, a UUID).
 *
 * Validation is format + checksum only (CPF/CNPJ reuse their validators); it
 * does NOT check whether the key is actually registered in the DICT — that is
 * an online lookup. Detected type is exposed as {@see DocumentMeta::$keyType}.
 *
 * @api
 */
final class PixValidation extends AbstractValidatableDocument
{
    private const EVP = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    // E.164: '+' then up to 15 digits total (leading digit is non-zero).
    private const PHONE = '/^\+[1-9][0-9]\d{1,13}$/';
    // Pragmatic e-mail shape (W3C-ish); DICT caps the length at 77.
    private const EMAIL = '/^[^@\s]+@[^@\s]+\.[^@\s]+$/';

    protected function documentName(): string
    {
        return 'pix';
    }

    /** PIX keys are not digit-only; keep the trimmed value verbatim. */
    protected function sanitize(string $value): string
    {
        return trim($value);
    }

    protected function doValidate(): ?ReasonCode
    {
        $value = $this->sanitize($this->raw());
        $type = self::detectType($value);

        return match ($type) {
            'cpf' => (new CPFValidation($value))->validate()->valid ? null : ReasonCode::BadCheckDigit,
            'cnpj' => (new CNPJValidation($value))->validate()->valid ? null : ReasonCode::BadCheckDigit,
            'email', 'phone', 'evp' => null,
            default => ReasonCode::InvalidFormat,
        };
    }

    /**
     * Classifies the key by shape. Order matters: '+' and '@' are unambiguous;
     * a UUID is checked before digit-only so it never falls through; then the
     * digit-only CPF (11) / CNPJ (14) lengths.
     *
     * @return 'cpf'|'cnpj'|'email'|'phone'|'evp'|null
     */
    private static function detectType(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        if ($value[0] === '+') {
            return preg_match(self::PHONE, $value) === 1 ? 'phone' : null;
        }
        if (str_contains($value, '@')) {
            return (strlen($value) <= 77 && preg_match(self::EMAIL, $value) === 1) ? 'email' : null;
        }
        if (preg_match(self::EVP, $value) === 1) {
            return 'evp';
        }
        if (preg_match('/^\d{11}$/', $value) === 1) {
            return 'cpf';
        }
        if (preg_match('/^\d{14}$/', $value) === 1) {
            return 'cnpj';
        }

        return null;
    }

    /** The detected key type (null when the key is invalid). */
    protected function extractMeta(string $normalized): DocumentMeta
    {
        return new DocumentMeta(keyType: self::detectType($normalized));
    }
}
