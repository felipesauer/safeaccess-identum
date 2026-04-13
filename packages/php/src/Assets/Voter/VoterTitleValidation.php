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
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        if (strlen($digits) !== 12) {
            return false;
        }
        // TSE: homogeneous sequences are not used
        if (preg_match('/^(\d)\1{11}$/', $digits) === 1) {
            return false;
        }

        $serial = substr($digits, 0, 8);
        $uf = substr($digits, 8, 2);
        $dvIn1 = (int) $digits[10];
        $dvIn2 = (int) $digits[11];

        // DV1: weights 2..9 over the 8-digit serial
        $w1 = [2, 3, 4, 5, 6, 7, 8, 9];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += ((int) $serial[$i]) * $w1[$i];
        }
        $dv1 = $sum % 11;
        if ($dv1 === 10) {
            $dv1 = 0;
        }

        // DV2: depends on the UF digits and DV1
        $u1 = (int) $uf[0];
        $u2 = (int) $uf[1];
        $sum = $u1 * 7 + $u2 * 8 + $dv1 * 9;
        $dv2 = $sum % 11;
        if ($dv2 === 10) {
            $dv2 = 0;
        }

        return $dvIn1 === $dv1 && $dvIn2 === $dv2;
    }
}
