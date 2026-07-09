<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\Certidao;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\DocumentMeta;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Brazilian civil registry certificate matrículas (nationwide 32-digit
 * model — CNJ Provimento 63/2017, consolidated by Provimento 149/2023).
 *
 * Layout (32 digits): CNS serventia(6) · acervo(2) · RCPN(2) · year(4) ·
 * book type(1) · book(5) · sheet(3) · term(7) · D1(1) · D2(1).
 *
 * Check digits use Mod-11 with a ×10 step: each DV is (weightedSum × 10) % 11,
 * where a remainder of 10 maps to 1. Not to be confused with the 20-digit
 * judicial process number (Mod-97) or the real-estate CNM (Mod-97).
 *
 * @api
 */
final class CertidaoValidation extends AbstractValidatableDocument
{
    /** Book-type digit (position 15) → certificate kind. */
    private const BOOK_TYPES = [
        '1' => 'birth',      // Livro A
        '2' => 'marriage',   // Livro B
        '3' => 'marriage',   // Livro B-Auxiliar
        '4' => 'death',      // Livro C
        '5' => 'stillbirth', // Livro C-Auxiliar (natimorto)
        '7' => 'other',      // Livro E
    ];

    protected function documentName(): string
    {
        return 'certidao';
    }

    protected function doValidate(): ?ReasonCode
    {
        $digits = $this->sanitize($this->raw());

        // Matrícula must have exactly 32 digits.
        if (strlen($digits) !== 32) {
            return ReasonCode::WrongLength;
        }

        $d1 = self::checkDigit(substr($digits, 0, 30), 31);
        $d2 = self::checkDigit(substr($digits, 0, 31), 32);

        if ($digits[30] !== (string) $d1 || $digits[31] !== (string) $d2) {
            return ReasonCode::BadCheckDigit;
        }

        return null;
    }

    /**
     * Mod-11 ×10 check digit: weighted sum of $digits with descending weights
     * from $startWeight down to 2, then (sum × 10) % 11; a remainder of 10 → 1.
     */
    private static function checkDigit(string $digits, int $startWeight): int
    {
        $sum = 0;
        for ($i = 0, $w = $startWeight; $i < strlen($digits); $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }
        $dv = ($sum * 10) % 11;

        return $dv === 10 ? 1 : $dv;
    }

    /** Certificate kind derived from the book-type digit (position 15). */
    protected function extractMeta(string $normalized): DocumentMeta
    {
        return new DocumentMeta(type: self::BOOK_TYPES[$normalized[14]] ?? null);
    }
}
