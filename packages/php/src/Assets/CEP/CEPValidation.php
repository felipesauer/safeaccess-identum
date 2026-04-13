<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CEP;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian CEP (Código de Endereçamento Postal) numbers.
 *
 * @api
 */
final class CEPValidation extends AbstractValidatableDocument
{
    protected function doValidate(): bool
    {
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // 8 dígitos, qualquer faixa (regras locais ficam a cargo de quem usa)
        return strlen($digits) === 8;
    }
}
