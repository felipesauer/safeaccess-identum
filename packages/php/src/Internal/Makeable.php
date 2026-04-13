<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Internal;

/**
 * Exposes ::make() as a shorthand for new static(). Required because __callStatic
 * cannot use new static() from the caller directly — late binding would break.
 *
 * @internal
 */
trait Makeable
{
    public static function make(mixed ...$parameters): static
    {
        // @phpstan-ignore-next-line
        return new static(...$parameters);
    }
}
