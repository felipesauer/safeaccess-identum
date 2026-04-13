<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\Voter;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian Voter Title (Título de Eleitor) numbers.
 *
 * @api
 */
final class VoterTitleValidation extends AbstractValidatableDocument
{
    protected function doValidate(): bool
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // Voter Title must have exactly 12 digits
        if (strlen($digits) !== 12) {
            return false;
        }

        // Guard: TSE (Supreme Electoral Court) does not use all-same-digit sequences
        if (preg_match('/^(\d)\1{11}$/', $digits) === 1) {
            return false;
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
        return $dvIn1 === $dv1 && $dvIn2 === $dv2;
    }
}
