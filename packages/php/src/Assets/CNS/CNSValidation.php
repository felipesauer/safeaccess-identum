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
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        if (strlen($digits) !== 15) {
            return false;
        }

        $first = (int) $digits[0];

        // Cards starting with 1 or 2 are derived from a PIS/PASEP registration
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

        // Cards starting with 7, 8, or 9 are provisional (not tied to PIS)
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
