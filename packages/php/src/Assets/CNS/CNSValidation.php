<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CNS;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\DocumentMeta;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Brazilian CNS (Cartão Nacional de Saúde) numbers.
 *
 * @api
 */
final class CNSValidation extends AbstractValidatableDocument
{
    protected function documentName(): string
    {
        return 'cns';
    }

    /**
     * Generates a valid CNS (provisional type, leading 7/8/9).
     *
     * Provisional cards only require the weighted sum (15..1) to be divisible by
     * 11, so we fix the first 14 digits at random and solve the last one; if no
     * single digit closes the sum, we resample. This avoids reproducing the
     * Ministry of Health's definitive-card (type 1/2) suffix rules.
     *
     * @param bool $formatted When true, returns the masked form (000 0000 0000 0000).
     */
    public static function generate(bool $formatted = false): string
    {
        while (true) {
            $head = (string) random_int(7, 9);
            for ($i = 1; $i < 14; $i++) {
                $head .= random_int(0, 9);
            }

            // Weighted sum (weights 15..2) of the first 14 digits; the 15th weight is 1,
            // so the last digit must make the total divisible by 11.
            $sum = 0;
            for ($i = 0, $w = 15; $i < 14; $i++, $w--) {
                $sum += ((int) $head[$i]) * $w;
            }
            $last = (11 - ($sum % 11)) % 11;

            if ($last < 10) {
                $value = $head . $last;

                return $formatted ? (new self($value))->format() : $value;
            }
            // last === 10 cannot be a single digit → resample.
        }
    }

    protected function doValidate(): ?ReasonCode
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = $this->sanitize($this->raw());

        // CNS (National Health Card) must have exactly 15 digits
        if (strlen($digits) !== 15) {
            return ReasonCode::WrongLength;
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

            return $digits === $resultado ? null : ReasonCode::BadCheckDigit;
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
            return ($sum % 11) === 0 ? null : ReasonCode::BadCheckDigit;
        }

        // Leading digit is not a recognized CNS range (1,2,7,8,9).
        return ReasonCode::InvalidFormat;
    }

    /** CNS subtype from the leading digit: 1/2 definitive, 7/8/9 provisional. */
    protected function extractMeta(string $normalized): DocumentMeta
    {
        $first = (int) $normalized[0];
        $type = ($first === 1 || $first === 2) ? 'definitive' : 'provisional';

        return new DocumentMeta(type: $type);
    }

    /** Canonical CNS mask: 000 0000 0000 0000 (space-separated groups). */
    protected function mask(string $stripped): string
    {
        return preg_replace('/^(\d{3})(\d{4})(\d{4})(\d{4})$/', '$1 $2 $3 $4', $stripped) ?? $stripped;
    }
}
