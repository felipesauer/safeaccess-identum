<?php

declare(strict_types=1);

use SafeAccess\Identum\Internal\Aliasable;
use SafeAccess\Identum\Internal\Makeable;

describe(Aliasable::class, function () {

    beforeEach(function () {
        // targets
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

        // registries (cada teste ganha FQCNs únicos)
        $regA = new class () {
            use Aliasable;
        };
        $regB = new class () {
            use Aliasable;
        };

        // guardamos só os class-strings pra chamar estático depois
        $this->WithoutMakeable = get_class($without);
        $this->WithMakeable = get_class($with);
        $this->RegistryA = get_class($regA);
        $this->RegistryB = get_class($regB);
    });

    it('registers and resolves with precedence', function () {
        $cls = $this->RegistryA;

        $cls::alias('foo', $this->WithoutMakeable);
        $cls::alias('foo', $this->WithMakeable); // sobrescreve

        expect($cls::getAlias('foo'))->toBe($this->WithMakeable);

        $obj = $cls::foo('x');
        expect($obj)->toBeInstanceOf($this->WithMakeable)
            ->and($obj->captured)->toBe(['x']);
    });

    it('resolves via "new" when target has no Makeable', function () {
        $cls = $this->RegistryA;
        $cls::alias('plain', $this->WithoutMakeable);

        $obj = $cls::plain(1, 2);
        expect($obj)->toBeInstanceOf($this->WithoutMakeable)
            ->and($obj->captured)->toBe([1, 2]);
    });

    it('throws when alias is missing', function () {
        $cls = $this->RegistryA;
        expect(fn () => $cls::nope())->toThrow(RuntimeException::class);
    });

    it('keeps registries isolated per class', function () {
        $a = $this->RegistryA;
        $b = $this->RegistryB;

        $a::alias('x', $this->WithoutMakeable);
        $b::alias('y', $this->WithMakeable);

        expect($a::getAlias())->toHaveKey('x')->and($a::getAlias())->not->toHaveKey('y');
        expect($b::getAlias())->toHaveKey('y')->and($b::getAlias())->not->toHaveKey('x');
    });
});
