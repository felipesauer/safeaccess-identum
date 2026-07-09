<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Exceptions;

use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Thrown by {@see IEValidation} when the state code passed in does not
 * map to any registered IE rule (i.e., not a valid IBGE UF code).
 *
 * Always carries {@see ReasonCode::UnknownUf}.
 *
 * @api
 *
 * @see ValidationException
 * @see \SafeAccess\Identum\Assets\IE\StateEnum
 */
class InvalidStateRuleException extends ValidationException
{
    public function __construct(string $document = 'ie', string $normalized = '')
    {
        parent::__construct($document, ReasonCode::UnknownUf, $normalized);
    }
}
