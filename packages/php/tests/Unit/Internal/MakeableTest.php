<?php

declare(strict_types=1);

use SafeAccess\Identum\Internal\Makeable;

describe(Makeable::class, function () {
    beforeEach(function () {
        $baseObj = new class () {
            use Makeable;

            /** @var array<int, mixed> */
            public array $rest = [];

            public function __construct(
                public string $name = 'anonymous',
                public int $level = 0,
                mixed ...$rest
            ) {
                $this->rest = $rest;
            }
        };
        $this->Base = get_class($baseObj);

        $childObj = new class () {
            use Makeable;

            /** @var array<int, mixed> */
            public array $rest = [];

            public function __construct(
                public string $name = 'child',
                public int $level = 1,
                mixed ...$rest
            ) {
                $this->rest = $rest;
            }
        };
        $this->Child = get_class($childObj);
    });

    it('creates an instance with defaults', function () {
        $cls = $this->Base;
        $o = $cls::make();

        expect($o)->toBeInstanceOf($cls)
            ->and($o->name)->toBe('anonymous')
            ->and($o->level)->toBe(0)
            ->and($o->rest)->toBe([]);
    });

    it('forwards positional constructor arguments', function () {
        $cls = $this->Base;
        $o = $cls::make('foo', 2, ['flag' => true]);

        expect($o->name)->toBe('foo')
            ->and($o->level)->toBe(2)
            ->and($o->rest)->toBe([['flag' => true]]);
    });

    it('supports variadic arguments and preserves order', function () {
        $cls = $this->Base;
        $objArg = new stdClass();
        $o = $cls::make('X', 9, 1, 2, ['k' => 'v'], $objArg);

        expect($o->rest)->toHaveCount(4);
        expect($o->rest[0])->toBe(1);
        expect($o->rest[1])->toBe(2);
        expect($o->rest[2])->toBe(['k' => 'v']);
        expect($o->rest[3])->toBe($objArg);
    });

    it('returns late static binding instance for another class using the trait', function () {
        $cls = $this->Child;
        $c = $cls::make('foo', 3, 'extra');

        expect($c)->toBeInstanceOf($cls)
            ->and($c->name)->toBe('foo')
            ->and($c->level)->toBe(3)
            ->and($c->rest)->toBe(['extra']);
    });

    it('creates distinct instances on each call', function () {
        $cls = $this->Base;
        $a = $cls::make('foo', 1);
        $b = $cls::make('foo', 1);

        expect(spl_object_id($a))->not->toBe(spl_object_id($b));
    });
});
