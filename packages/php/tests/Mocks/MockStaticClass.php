<?php

declare(strict_types=1);

namespace Tests\Mocks;

class MockStaticClass
{
    /**
     * @var self|null
     */
    protected static ?self $instance = null;

    /**
     * @param mixed ...$parameters
     * @return static
     */
    public static function make(mixed ...$parameters): static
    {
        // @phpstan-ignore-next-line
        $instance = new static(...$parameters);

        self::$instance = $instance;

        return $instance;
    }
}
