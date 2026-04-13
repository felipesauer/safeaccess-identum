<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CNH;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian CNH (Carteira Nacional de Habilitação) numbers.
 *
 * @api
 */
final class CNHValidation extends AbstractValidatableDocument
{
    protected function doValidate(): bool
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // CNH must have exactly 11 digits
        if (strlen($digits) !== 11) {
            return false;
        }

        // Guard: DETRAN (Brazilian traffic authority) does not issue sequential same-digit blocks
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        $base = substr($digits, 0, 9);
        $dvInformed1 = (int) $digits[9];
        $dvInformed2 = (int) $digits[10];

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of first 9 digits (weights 9 down to 1) modulo 11.
        // Special overflow rule: if remainder > 9, set DV1 = 0 and flag firstIsTenPlus = true.
        $sum1 = 0;
        for ($i = 0, $w = 9; $i < 9; $i++, $w--) {
            $sum1 += ((int) $base[$i]) * $w;
        }
        $dv1 = $sum1 % 11;
        $firstIsTenPlus = false;
        if ($dv1 > 9) {
            $dv1 = 0;
            $firstIsTenPlus = true;
        }

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted sum of first 9 digits (weights 1 up to 9) modulo 11.
        // Overflow adjustment: if DV1 overflowed, subtract 2 from DV2 (with wrapping: if < 0, add 9).
        $sum2 = 0;
        for ($i = 0, $w = 1; $i < 9; $i++, $w++) {
            $sum2 += ((int) $base[$i]) * $w;
        }
        $dv2 = $sum2 % 11;

        // When DV1 overflowed (went to 0), apply DV2 adjustment: subtract 2 with modulo 9 wrapping
        if ($firstIsTenPlus) {
            if ($dv2 - 2 < 0) {
                $dv2 += 9;
            } else {
                $dv2 -= 2;
            }
        }

        // Final guard: DV2 overflow (if > 9) also becomes 0
        if ($dv2 > 9) {
            $dv2 = 0;
        }

        // Final verification: check if computed DV1/DV2 match the informed check digits
        return $dvInformed1 === $dv1 && $dvInformed2 === $dv2;
    }
}
