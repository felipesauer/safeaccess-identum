<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\Voter;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Brazilian Voter Title (Título de Eleitor) numbers.
 *
 * @api
 */
final class VoterTitleValidation extends AbstractValidatableDocument
{
    protected function documentName(): string
    {
        return 'voter-title';
    }

    /**
     * Generates a valid Voter Title.
     *
     * @param bool $formatted Voter Title has no canonical mask; kept for API symmetry.
     */
    public static function generate(bool $formatted = false): string
    {
        // 8-digit sequential serial + a valid UF code (01–28).
        $serial = str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
        $uf = str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT);

        // DV1 over the serial (weights 2..9), remainder 10 → 0.
        $w1 = [2, 3, 4, 5, 6, 7, 8, 9];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += ((int) $serial[$i]) * $w1[$i];
        }
        $dv1 = $sum % 11;
        if ($dv1 === 10) {
            $dv1 = 0;
        }

        // DV2 over the UF digits + DV1 (weights 7,8,9), remainder 10 → 0.
        $dv2 = ((int) $uf[0] * 7 + (int) $uf[1] * 8 + $dv1 * 9) % 11;
        if ($dv2 === 10) {
            $dv2 = 0;
        }

        $value = $serial . $uf . $dv1 . $dv2;

        return $formatted ? (new self($value))->format() : $value;
    }

    protected function doValidate(): ?ReasonCode
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = $this->sanitize($this->raw());

        // Voter Title must have exactly 12 digits
        if (strlen($digits) !== 12) {
            return ReasonCode::WrongLength;
        }

        // Guard: TSE (Supreme Electoral Court) does not use all-same-digit sequences
        if (preg_match('/^(\d)\1{11}$/', $digits) === 1) {
            return ReasonCode::KnownInvalid;
        }

        $serial = substr($digits, 0, 8);
        $uf = substr($digits, 8, 2);
        $dvIn1 = (int) $digits[10];
        $dvIn2 = (int) $digits[11];

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of 8-digit serial (weights [2,3,4,5,6,7,8,9]) modulo 11.
        // If remainder = 10, DV1 = 0; otherwise DV1 = remainder.
        $w1 = [2, 3, 4, 5, 6, 7, 8, 9];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += ((int) $serial[$i]) * $w1[$i];
        }
        $dv1 = $sum % 11;
        if ($dv1 === 10) {
            $dv1 = 0;
        }

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted combination of UF digits + DV1 (weights 7, 8, 9) modulo 11.
        // DV2 = (u1×7 + u2×8 + dv1×9) % 11; if result = 10, DV2 = 0.
        $u1 = (int) $uf[0];
        $u2 = (int) $uf[1];
        $sum = $u1 * 7 + $u2 * 8 + $dv1 * 9;
        $dv2 = $sum % 11;
        if ($dv2 === 10) {
            $dv2 = 0;
        }

        // Final verification: check if computed DV1/DV2 match the informed check digits
        return $dvIn1 === $dv1 && $dvIn2 === $dv2 ? null : ReasonCode::BadCheckDigit;
    }
}
