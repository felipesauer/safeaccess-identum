<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CNPJ;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\DocumentMeta;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Brazilian CNPJ (Cadastro Nacional da Pessoa Jurídica) numbers.
 *
 * Supports both numeric and alphanumeric CNPJ formats.
 *
 * @api
 */
final class CNPJValidation extends AbstractValidatableDocument
{
    /**
     * Validates Brazilian CNPJ numbers — both numeric and alphanumeric variants.
     *
     * Numeric CNPJ: 14 pure digits. Alphanumeric CNPJ: 12 chars [A-Z0-9] + 2 check digits [0-9].
     * Character value mapping: ord(ch) - 48 → yields 0..9 for digits, 17..42 for A..Z.
     * Both variants use Mod11 with weights w1=[5,4,3,2,9,8,7,6,5,4,3,2] and w2=[6,5,4,3,2,9,8,7,6,5,4,3,2].
     */
    protected function documentName(): string
    {
        return 'cnpj';
    }

    /**
     * Generates a valid (numeric) CNPJ.
     *
     * @param bool $formatted When true, returns the masked form (00.000.000/0000-00).
     */
    public static function generate(bool $formatted = false): string
    {
        $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        do {
            $base = '';
            for ($i = 0; $i < 12; $i++) {
                $base .= random_int(0, 9);
            }
        } while (preg_match('/^(\d)\1{11}$/', $base) === 1);

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ((int) $base[$i]) * $w1[$i];
        }
        $rest = $sum % 11;
        $dv1 = ($rest < 2) ? 0 : 11 - $rest;

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ((int) $base[$i]) * $w2[$i];
        }
        $sum += $dv1 * $w2[12];
        $rest = $sum % 11;
        $dv2 = ($rest < 2) ? 0 : 11 - $rest;

        $value = $base . $dv1 . $dv2;

        return $formatted ? (new self($value))->format() : $value;
    }

    /**
     * CNPJ keeps letters (alphanumeric format), so it cannot strip to digits only.
     * Uppercases and removes only formatting separators; other characters are
     * preserved so they are caught as invalid during validation.
     */
    protected function sanitize(string $value): string
    {
        return preg_replace('/[\s.\-\/]/', '', strtoupper($value)) ?? '';
    }

    protected function doValidate(): ?ReasonCode
    {
        $txt = $this->sanitize($this->raw());

        // Guard: CNPJ must be exactly 14 characters long
        if (strlen($txt) !== 14) {
            return ReasonCode::WrongLength;
        }

        // Guard: only [A-Z0-9] are allowed; check digits (positions 12–13) must be numeric
        if (preg_match('/^[A-Z0-9]{12}[0-9]{2}$/', $txt) !== 1) {
            return ReasonCode::InvalidFormat;
        }

        // Guard: if purely numeric, reject the all-same-digit pattern (legacy Receita Federal rule)
        if (ctype_digit($txt) && preg_match('/^(\d)\1{13}$/', $txt) === 1) {
            return ReasonCode::KnownInvalid;
        }

        $body12 = substr($txt, 0, 12);
        $dvIn1  = (int) $txt[12];
        $dvIn2  = (int) $txt[13];

        // Character → integer value mapper: ord(char) - 48.
        // Digits '0'–'9' map to 0–9; letters 'A'–'Z' map to 17–42 (their ASCII value − 48).
        // The InvalidFormat guard above already ensured every body char is [A-Z0-9].
        $val = static fn (string $ch): int => ord($ch) - 48;

        // Weights for DV1 and DV2 calculations
        $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // ===== First Verification Digit (DV1) =====
        // Sum of (character_value × weight) for the first 12 positions, then modulo 11.
        // If remainder < 2, DV1 = 0; otherwise DV1 = 11 − remainder.
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $val($body12[$i]) * $w1[$i];
        }
        $rest = $sum % 11;
        $dv1  = ($rest < 2) ? 0 : 11 - $rest;

        // ===== Second Verification Digit (DV2) =====
        // Sum of (character_value × weight) for the first 12 positions PLUS (DV1 × w2[12]).
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $val($body12[$i]) * $w2[$i];
        }
        // Add DV1 contribution (index 12 of w2)
        $sum += $dv1 * $w2[12];

        $rest = $sum % 11;
        $dv2  = ($rest < 2) ? 0 : 11 - $rest;

        // Final verification: check if computed DV1/DV2 match the input check digits
        return $dvIn1 === $dv1 && $dvIn2 === $dv2 ? null : ReasonCode::BadCheckDigit;
    }

    /**
     * CNPJ metadata: whether it is a headquarters (branch marker '0001' before the
     * check digits) and whether it uses the alphanumeric format (any letter present).
     */
    protected function extractMeta(string $normalized): DocumentMeta
    {
        $branch = substr($normalized, 8, 4);

        return new DocumentMeta(
            isMatriz: $branch === '0001',
            isAlphanumeric: preg_match('/[A-Z]/', $normalized) === 1,
        );
    }

    /**
     * Canonical CNPJ mask: XX.XXX.XXX/XXXX-YY. Alphanumeric-aware — the first 12
     * positions may be [A-Z0-9] and only the last 2 (check digits) are numeric.
     */
    protected function mask(string $stripped): string
    {
        return preg_replace(
            '/^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{4})(\d{2})$/',
            '$1.$2.$3/$4-$5',
            $stripped,
        ) ?? $stripped;
    }
}
