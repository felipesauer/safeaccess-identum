<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class RoRule extends AbstractStateRule
{
    /**
     * {@inheritDoc}
     */
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || $this->allSameDigits($digits)) {
            return false;
        }

        $len = strlen($digits);

        // Formato atual (14 dígitos) ou legado (9 dígitos)
        if ($len === 14) {
            return $this->validateAtual14($digits);
        }
        if ($len === 9) {
            return $this->validateLegado9($digits);
        }

        return false;
    }

    /**
     * RO — formato atual (14 dígitos)
     * DV: módulo 11 com política "resto < 2 => 0; senão 11 - resto"
     * Pesos: [6,5,4,3,2,9,8,7,6,5,4,3,2] sobre os 13 primeiros dígitos.
     *
     * @param string $digits
     * @return boolean
     */
    private function validateAtual14(string $digits): bool
    {
        $base13 = substr($digits, 0, 13);
        $dvCalc = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base13),
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int)$digits[13] === $dvCalc;
    }

    /**
     * RO — formato legado (9 dígitos)
     * Estrutura: 3 dígitos de município + 5 dígitos da empresa + 1 DV.
     * Cálculo: ignorar os 3 de município; aplicar pesos [6,5,4,3,2] nos 5 da empresa;
     *          DV = 11 - (soma % 11); se DV >= 10 => DV -= 10 (fica 0 ou 1).
     */
    private function validateLegado9(string $digits): bool
    {
        $empresa5 = substr($digits, 3, 5);
        $sum = $this->sumProducts($this->toIntArray($empresa5), [6, 5, 4, 3, 2]);
        $rest = $sum % 11;

        $dv = 11 - $rest;
        if ($dv >= 10) {
            $dv -= 10; // 10->0, 11->1
        }

        return (int)$digits[8] === $dv;
    }

    /**
     * Mod 11 com política padrão de várias UFs:
     * - se resto < 2 => 0
     * - senão => 11 - resto
     *
     * @param array<int> $digits
     * @param array<int> $weights
     * @return int
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;

        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
