<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CPF;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\DocumentMeta;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Brazilian CPF (Cadastro de Pessoas Físicas) numbers.
 *
 * Applies Mod11 check-digit algorithm with two verification digits.
 *
 * @api
 */
final class CPFValidation extends AbstractValidatableDocument
{
    /**
     * Fiscal region by the 9th digit (index 8). Each digit maps to a group of
     * states, not a single UF, so the value is the region's state list.
     *
     * @var array<int, string>
     */
    private const FISCAL_REGIONS = [
        0 => 'RS',
        1 => 'DF-GO-MS-MT-TO',
        2 => 'AC-AM-AP-PA-RO-RR',
        3 => 'CE-MA-PI',
        4 => 'AL-PB-PE-RN',
        5 => 'BA-SE',
        6 => 'MG',
        7 => 'ES-RJ',
        8 => 'SP',
        9 => 'PR-SC',
    ];

    protected function documentName(): string
    {
        return 'cpf';
    }

    /**
     * Generates a valid CPF.
     *
     * @param bool $formatted When true, returns the masked form (000.000.000-00).
     * @return string A number that always passes {@see validate()}.
     */
    public static function generate(bool $formatted = false): string
    {
        // 9 random base digits, avoiding the all-same-digit sequence (reserved as invalid).
        do {
            $base = '';
            for ($i = 0; $i < 9; $i++) {
                $base .= random_int(0, 9);
            }
        } while (preg_match('/^(\d)\1{8}$/', $base) === 1);

        $dv1 = self::checkDigit($base, 10);
        $dv2 = self::checkDigit($base . $dv1, 11);

        $value = $base . $dv1 . $dv2;

        return $formatted ? (new self($value))->format() : $value;
    }

    /**
     * Mod-11 check digit over $digits with descending weights starting at $startWeight.
     * Remainder < 2 yields 0 (the CPF convention shared with {@see doValidate()}).
     */
    private static function checkDigit(string $digits, int $startWeight): int
    {
        $sum = 0;
        for ($i = 0, $w = $startWeight; $i < strlen($digits); $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }
        $rest = $sum % 11;

        return ($rest < 2) ? 0 : 11 - $rest;
    }

    /**
     * Domain validation for CPF:
     * - Must have 11 digits
     * - Must not be a repeated sequence (e.g., 000..., 111..., ...)
     * - Must match both check digits (Mod11)
     */
    protected function doValidate(): ?ReasonCode
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = $this->sanitize($this->raw());

        // CPF must have exactly 11 digits
        if (strlen($digits) !== 11) {
            return ReasonCode::WrongLength;
        }

        // Guard: Receita Federal (Brazilian tax authority) reserves all 11-same-digit sequences
        // (e.g., 000...000, 111...111) as invalid forever — no valid CPF exists with all same digits.
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return ReasonCode::KnownInvalid;
        }

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of first 9 digits (weights 10 down to 2) modulo 11.
        // If remainder < 2, DV1 = 0; otherwise DV1 = 11 - remainder.
        $sum = 0;
        for ($i = 0, $w = 10; $i < 9; $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }
        $rest = $sum % 11;
        $dv1  = ($rest < 2) ? 0 : 11 - $rest;

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted sum of first 9 digits PLUS DV1 (weights 11 down to 2) modulo 11.
        // If remainder < 2, DV2 = 0; otherwise DV2 = 11 - remainder.
        // Note: the 10th digit slot (index 9) in the weighted sum is DV1, followed by original digits 10–11 (indices 8–9).
        $sum = 0;
        for ($i = 0, $w = 11; $i < 10; $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }
        $rest = $sum % 11;
        $dv2  = ($rest < 2) ? 0 : 11 - $rest;

        // Final verification: check if the computed DV1/DV2 match the digits at positions 9 and 10
        if ($digits[9] !== (string) $dv1 || $digits[10] !== (string) $dv2) {
            return ReasonCode::BadCheckDigit;
        }

        return null;
    }

    /** Fiscal region (group of states) inferred from the 9th digit. */
    protected function extractMeta(string $normalized): DocumentMeta
    {
        $region = self::FISCAL_REGIONS[(int) $normalized[8]] ?? null;

        return new DocumentMeta(uf: $region);
    }

    /** Canonical CPF mask: 000.000.000-00. */
    protected function mask(string $stripped): string
    {
        return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $stripped) ?? $stripped;
    }
}
