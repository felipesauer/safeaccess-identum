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
     * Compatível com CNPJ numérico e alfanumérico:
     * - 14 posições; 12 primeiras: [A-Z0-9]; 2 últimas (DVs) sempre dígitos
     * - Valor do caractere: ord(ch) - 48  ( '0'..'9' => 0..9 ; 'A'..'Z' => 17..42 )
     * - Pesos w1/w2 e módulo 11 iguais ao CNPJ numérico
     */
    protected function doValidate(): bool
    {
        $raw = strtoupper($this->raw());

        // Remover separadores usuais e QUALQUER whitespace (\s), mantendo outros chars para validação
        // (isso permite cobrir o ramo val() === -1 com caracteres inválidos como '@', '#', etc.)
        $txt = preg_replace('/[\s.\-\/]/', '', $raw) ?? '';

        // tamanho
        if (strlen($txt) !== 14) {
            return false;
        }

        // DVs (pos 13 e 14) devem ser dígitos
        if (!ctype_digit($txt[12] . $txt[13])) {
            return false;
        }

        // sequência repetida só faz sentido para numérico puro (legado)
        if (ctype_digit($txt) && preg_match('/^(\d)\1{13}$/', $txt) === 1) {
            return false;
        }

        $body12 = substr($txt, 0, 12);
        $dvIn1  = (int) $txt[12];
        $dvIn2  = (int) $txt[13];

        // mapeia caractere -> valor (ASCII-48)
        $val = static function (string $ch): int {
            $o = ord($ch);
            if ($o >= 48 && $o <= 57) { // '0'..'9'
                return $o - 48;
            }
            if ($o >= 65 && $o <= 90) { // 'A'..'Z'
                return $o - 48; // 17..42
            }
            return -1; // inválido
        };

        $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // DV1
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $v = $val($body12[$i]);
            if ($v < 0) {
                return false; // cobre a linha 63 quando aparece char inválido
            }
            $sum += $v * $w1[$i];
        }
        $rest = $sum % 11;
        $dv1  = ($rest < 2) ? 0 : 11 - $rest;

        // DV2 (12 posições + dv1)
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $val($body12[$i]) * $w2[$i];
        }
        $sum += $dv1 * $w2[12];

        $rest = $sum % 11;
        $dv2  = ($rest < 2) ? 0 : 11 - $rest;

        return $dvIn1 === $dv1 && $dvIn2 === $dv2;
    }
}
