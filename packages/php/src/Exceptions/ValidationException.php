<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Exceptions;

/**
 * Root exception for all validation errors. Catch this to handle any
 * failure coming out of the library without importing every subclass.
 *
 * @api
 *
 * @see InvalidStateRuleException
 */
class ValidationException extends \RuntimeException
{
}
