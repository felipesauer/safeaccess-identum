<?php

declare(strict_types=1);

use SafeAccess\Identum\Internal\AbstractResolver;
use SafeAccess\Identum\Internal\Makeable;

describe(AbstractResolver::class, function () {

    beforeEach(function () {
        // Targets
        $without = new class () {
            public array $captured = [];
            public function __construct(...$args)
            {
                $this->captured = $args;
            }
        };
        $with = new class () {
            use Makeable;
            public array $captured = [];
            public function __construct(...$args)
            {
                $this->captured = $args;
            }
        };

        // Two resolver subclasses (unique per test to avoid static leaks)
        $resolverA = new class () extends AbstractResolver {};
        $resolverB = new class () extends AbstractResolver {};

        // Store class-strings to call statics later
        $this->Without = get_class($without);
        $this->With = get_class($with);
        $this->ResA = get_class($resolverA);
        $this->ResB = get_class($resolverB);
    });

    it('registers the constructed instance into the static registry', function () {
        $cls = $this->ResA;
        $instance = new $cls();

        $ref = new ReflectionClass($cls);
        $prop = $ref->getProperty('instances');
        $prop->setAccessible(true);
        $map = $prop->getValue();

        expect($map[$cls] ?? null)->toBe($instance);
    });

    it('merges alias mapping passed to constructor and resolves both targets', function () {
        $cls = $this->ResA;

        new $cls([
            'mk'    => $this->With,
            'plain' => $this->Without,
        ]);

        // resolve Makeable path
        $o1 = $cls::mk('a', 1);
        expect($o1)->toBeInstanceOf($this->With)
            ->and($o1->captured)->toBe(['a', 1]);

        // resolve non-Makeable path
        $o2 = $cls::plain('b', 2);
        expect($o2)->toBeInstanceOf($this->Without)
            ->and($o2->captured)->toBe(['b', 2]);
    });

    it('accepts null and keeps alias map empty', function () {
        $cls = $this->ResA;
        new $cls(null);

        $map = $cls::getAlias();
        expect($map)->toBeArray()->toBeEmpty();
    });

    it('alias() overrides constructor-provided mapping (prepend precedence)', function () {
        $cls = $this->ResA;

        new $cls(['foo' => $this->Without]);
        $ret = $cls::alias('foo', $this->With); // override

        expect($ret)->toBeInstanceOf($cls)
            ->and($cls::getAlias('foo'))->toBe($this->With);
    });

    it('boot() is idempotent and keeps the registered instance', function () {
        $cls = $this->ResA;
        $first = new $cls();

        $cls::boot(); // should not replace

        $ref = new ReflectionClass($cls);
        $prop = $ref->getProperty('instances');
        $prop->setAccessible(true);
        $current = $prop->getValue()[$cls] ?? null;

        expect($current)->toBe($first);
    });
});
