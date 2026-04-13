<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Exceptions;

/**
 * Thrown by {@see IEValidation} when the state code passed in does not
 * map to any registered IE rule (i.e., not a valid IBGE UF code).
 *
 * @api
 *
 * @see ValidationException
 * @see \SafeAccess\Identum\Assets\IE\StateEnum
 */
class InvalidStateRuleException extends ValidationException
{
}
