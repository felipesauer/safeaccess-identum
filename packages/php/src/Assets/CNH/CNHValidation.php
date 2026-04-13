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
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // 11 dígitos e não pode ser sequência repetida
        if (strlen($digits) !== 11) {
            return false;
        }
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        // base (9 primeiros), DVs informados
        $base = substr($digits, 0, 9);
        $dvInformed1 = (int) $digits[9];
        $dvInformed2 = (int) $digits[10];

        // DV1: pesos 9..1
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

        // DV2: pesos 1..9
        $sum2 = 0;
        for ($i = 0, $w = 1; $i < 9; $i++, $w++) {
            $sum2 += ((int) $base[$i]) * $w;
        }
        $dv2 = $sum2 % 11;

        // Regra especial quando o módulo do 1º DV > 9 (ou seja, virou 0)
        if ($firstIsTenPlus) {
            if ($dv2 - 2 < 0) {
                $dv2 += 9;
            } else {
                $dv2 -= 2;
            }
        }

        if ($dv2 > 9) {
            $dv2 = 0;
        }

        return $dvInformed1 === $dv1 && $dvInformed2 === $dv2;
    }
}
