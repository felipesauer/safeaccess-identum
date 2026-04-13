<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CNS;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian CNS (Cartão Nacional de Saúde) numbers.
 *
 * @api
 */
final class CNSValidation extends AbstractValidatableDocument
{
    protected function doValidate(): bool
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // CNS (National Health Card) must have exactly 15 digits
        if (strlen($digits) !== 15) {
            return false;
        }

        $first = (int) $digits[0];

        // ===== CNS Type 1 & 2: Derived from PIS/PASEP Registration =====
        // Cards starting with 1 or 2 are linked to a valid PIS registration (digits 0-10).
        // Algorithm: weighted sum of PIS (first 11 digits, weights 15 down to 5) modulo 11.
        // DV calculation: 11 - remainder, with special Ministry of Health rules:
        //   - If DV = 11 → set to 0, suffix = '000'
        //   - If DV = 10 → add 2, recalculate DV = 11 - new_remainder, suffix = '001'
        //   - Otherwise DV, suffix = '000'
        if ($first === 1 || $first === 2) {
            $pis = substr($digits, 0, 11);

            $sum = 0;
            for ($i = 0, $w = 15; $i < 11; $i++, $w--) {
                $sum += ((int) $pis[$i]) * $w;
            }

            $rest = $sum % 11;
            $dv   = 11 - $rest;

            $resultado = '';
            if ($dv === 11) {
                $dv = 0;
                $resultado = $pis . '000' . $dv;
            } elseif ($dv === 10) {
                // Ministry of Health special rule: offset by 2 and recalculate
                $sum += 2;
                $dv = 11 - ($sum % 11);
                $resultado = $pis . '001' . $dv;
            } else {
                $resultado = $pis . '000' . $dv;
            }

            return $digits === $resultado;
        }

        // ===== CNS Type 7, 8, 9: Provisional (Not Tied to PIS) =====
        // Provisional cards are random 15-digit numbers. Validation is simple:
        // weighted sum (15 down to 1) must be divisible by 11 (mod 11 = 0).
        if ($first === 7 || $first === 8 || $first === 9) {
            // Weighted sum 15..1 must be divisible by 11
            $sum = 0;
            for ($i = 0, $w = 15; $i < 15; $i++, $w--) {
                $sum += ((int) $digits[$i]) * $w;
            }
            return ($sum % 11) === 0;
        }

        return false;
    }
}
