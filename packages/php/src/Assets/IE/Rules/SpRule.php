<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class SpRule extends AbstractStateRule
{
    /**
     * {@inheritDoc}
     */
    public function execute(string $ie): bool
    {
        $raw = strtoupper(trim($ie));

        // Produtor rural se iniciar com 'P'
        if ($raw !== '' && $raw[0] === 'P') {
            return $this->validateProdutorRural($raw);
        }

        return $this->validateComercialIndustrial($raw);
    }

    /**
     * @param string $raw
     * @return boolean
     */
    private function validateComercialIndustrial(string $raw): bool
    {
        $d = $this->digits($raw);
        if (strlen($d) !== 12 || $this->allSameDigits($d)) {
            return false;
        }

        // DV1 (posição 9) — pesos [1,3,4,5,6,7,8,10]; dv = (soma % 11); 10 => 0
        $dv1 = $this->dvSpResto($this->toIntArray(substr($d, 0, 8)), [1, 3, 4, 5, 6, 7, 8, 10]);
        if ((int)$d[8] !== $dv1) {
            return false;
        }

        // DV2 (posição 12) — pesos [3,2,10,9,8,7,6,5,4,3,2] sobre 8 + dv1 + digitos 10-11
        $body2 = substr($d, 0, 8) . $dv1 . substr($d, 9, 2);
        $dv2 = $this->dvSpResto($this->toIntArray($body2), [3, 2, 10, 9, 8, 7, 6, 5, 4, 3, 2]);

        return (int)$d[11] === $dv2;
    }

    /**
     * @param string $raw
     * @return boolean
     */
    private function validateProdutorRural(string $raw): bool
    {
        // remove tudo que não for dígito (a letra 'P' não entra no cálculo)
        $digits = $this->digits($raw);

        if (strlen($digits) !== 12 || $this->allSameDigits($digits)) {
            return false;
        }

        // DV único na 9ª posição numérica, calculado sobre os 8 primeiros dígitos
        $base8 = substr($digits, 0, 8);
        $dv = $this->dvSpResto($this->toIntArray($base8), [1, 3, 4, 5, 6, 7, 8, 10]);

        return (int)$digits[8] === $dv;
    }

    /**
     * Política SP: usa o próprio resto (sum % 11) como DV; se for 10, vira 0.
     *
     * @param array<int> $digits
     * @param array<int> $weights
     * @return int
     */
    private function dvSpResto(array $digits, array $weights): int
    {
        $rest = $this->sumProducts($digits, $weights) % 11;

        return ($rest === 10) ? 0 : $rest;
    }
}
