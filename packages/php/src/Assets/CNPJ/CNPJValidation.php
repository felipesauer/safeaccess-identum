<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CNPJ;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian CNPJ (Cadastro Nacional da Pessoa Jurídica) numbers.
 *
 * Supports both numeric and alphanumeric CNPJ formats.
 *
 * @api
 */
final class CNPJValidation extends AbstractValidatableDocument
{
    /**
     * Validates Brazilian CNPJ numbers — both numeric and alphanumeric variants.
     *
     * Numeric CNPJ: 14 pure digits. Alphanumeric CNPJ: 12 chars [A-Z0-9] + 2 check digits [0-9].
     * Character value mapping: ord(ch) - 48 → yields 0..9 for digits, 17..42 for A..Z.
     * Both variants use Mod11 with weights w1=[5,4,3,2,9,8,7,6,5,4,3,2] and w2=[6,5,4,3,2,9,8,7,6,5,4,3,2].
     */
    protected function doValidate(): bool
    {
        $raw = strtoupper($this->raw());

        // Strip common formatting separators (dot, dash, slash) and whitespace.
        // Preserve any other characters for validation (e.g., '@', '#') to catch them as invalid chars.
        $txt = preg_replace('/[\s.\-\/]/', '', $raw) ?? '';

        // Guard: CNPJ must be exactly 14 characters long
        if (strlen($txt) !== 14) {
            return false;
        }

        // Guard: check digits (positions 12–13) must always be numeric digits
        if (!ctype_digit($txt[12] . $txt[13])) {
            return false;
        }

        // Guard: if purely numeric, reject the all-same-digit pattern (legacy Receita Federal rule)
        if (ctype_digit($txt) && preg_match('/^(\d)\1{13}$/', $txt) === 1) {
            return false;
        }

        $body12 = substr($txt, 0, 12);
        $dvIn1  = (int) $txt[12];
        $dvIn2  = (int) $txt[13];

        // Character → integer value mapper: ord(char) - 48
        // Digits '0'–'9' map to 0–9; letters 'A'–'Z' map to 17–42 (their ASCII value − 48).
        $val = static function (string $ch): int {
            $o = ord($ch);
            if ($o >= 48 && $o <= 57) { // '0'..'9' → 0..9
                return $o - 48;
            }
            if ($o >= 65 && $o <= 90) { // 'A'..'Z' → 17..42
                return $o - 48;
            }
            return -1; // Invalid character (not alphanumeric)
        };

        // Weights for DV1 and DV2 calculations
        $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // ===== First Verification Digit (DV1) =====
        // Sum of (character_value × weight) for the first 12 positions, then modulo 11.
        // If remainder < 2, DV1 = 0; otherwise DV1 = 11 − remainder.
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $v = $val($body12[$i]);
            if ($v < 0) {
                return false; // Invalid character found → CNPJ is invalid
            }
            $sum += $v * $w1[$i];
        }
        $rest = $sum % 11;
        $dv1  = ($rest < 2) ? 0 : 11 - $rest;

        // ===== Second Verification Digit (DV2) =====
        // Sum of (character_value × weight) for the first 12 positions PLUS (DV1 × w2[12]).
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $val($body12[$i]) * $w2[$i];
        }
        // Add DV1 contribution (index 12 of w2)
        $sum += $dv1 * $w2[12];

        $rest = $sum % 11;
        $dv2  = ($rest < 2) ? 0 : 11 - $rest;

        // Final verification: check if computed DV1/DV2 match the input check digits
        return $dvIn1 === $dv1 && $dvIn2 === $dv2;
    }
}
