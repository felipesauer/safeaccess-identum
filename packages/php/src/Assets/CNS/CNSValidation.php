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

        // 1) Números iniciados por 1 ou 2 (derivados de PIS/PASEP)
        if ($first === 1 || $first === 2) {
            $pis = substr($digits, 0, 11);

            // soma ponderada 15..5 sobre os 11 dígitos (PIS)
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
                // regra especial: somar 2 e recalcular
                $sum += 2;
                $dv = 11 - ($sum % 11);
                $resultado = $pis . '001' . $dv;
            } else {
                $resultado = $pis . '000' . $dv;
            }

            return $digits === $resultado;
        }

        // 2) Números iniciados por 7, 8 ou 9 (provisórios)
        if ($first === 7 || $first === 8 || $first === 9) {
            // soma ponderada 15..1 sobre os 15 dígitos deve ser múltiplo de 11
            $sum = 0;
            for ($i = 0, $w = 15; $i < 15; $i++, $w--) {
                $sum += ((int) $digits[$i]) * $w;
            }
            return ($sum % 11) === 0;
        }

        // Outros dígitos iniciais são inválidos
        return false;
    }
}
