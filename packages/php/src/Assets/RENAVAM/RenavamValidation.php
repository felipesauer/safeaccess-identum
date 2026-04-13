<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\RENAVAM;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian RENAVAM (Registro Nacional de Veículos Automotores) numbers.
 *
 * @api
 */
final class RenavamValidation extends AbstractValidatableDocument
{
    protected function doValidate(): bool
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // RENAVAM must have exactly 11 digits
        if (strlen($digits) !== 11) {
            return false;
        }

        // Guard: DENATRAN (Brazilian national vehicle registry) does not assign all-same-digit sequences
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        $base = substr($digits, 0, 10);
        $dvIn = (int) $digits[10];

        // ===== Verification Digit (DV) =====
        // Algorithm: reverse the 10-digit base, then apply weights [2,3,4,5,6,7,8,9,2,3].
        // DV = 11 - (weighted_sum % 11); if DV ≥ 10, set to 0.
        $rev   = strrev($base);
        $pesos = [2, 3, 4, 5, 6, 7, 8, 9, 2, 3];

        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += ((int) $rev[$i]) * $pesos[$i];
        }

        $resto = $soma % 11;
        $dv    = 11 - $resto;
        if ($dv >= 10) {
            $dv = 0;
        }

        // Final verification: check if computed DV matches the check digit at position 10
        return $dv === $dvIn;
    }
}
