<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Internal;

/**
 * Provides a static factory method for flexible class instantiation.
 *
 * Enables `ClassName::make(...$args)` as an alternative to `new ClassName(...)`,
 * supporting late static binding across resolver hierarchies.
 *
 * @internal
 */
trait Makeable
{
    /**
     * @psalm-suppress
     *
     * Instantiate the class dynamically using optional parameters.
     *
     * This static factory method enables flexible instantiation of the class
     * with any number of constructor arguments. Useful in patterns where `new static(...)`
     * is required for late static binding and consistency across resolvers.
     *
     * @param mixed ...$parameters Optional parameters to pass to the class constructor.
     * @return static A new instance of the called class.
     */
    public static function make(mixed ...$parameters): static
    {
        // @phpstan-ignore-next-line
        return new static(...$parameters);
    }
}
