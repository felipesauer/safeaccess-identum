<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Global Teardown
|--------------------------------------------------------------------------
| Ensures all singletons and static state are reset between tests.
*/

/**
 * Reset the protected static $instances (from Aliasable trait) on a consumer.
 */
function resetAliasableInstances(string $fqcn): void
{
    $ref = new ReflectionClass($fqcn);
    $prop = $ref->getProperty('instances');
    $prop->setAccessible(true);
    $prop->setValue(null, []);
}
