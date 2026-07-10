<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CEP\CEPValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CEPValidation::class, function () {

    it('accepts CEP masked and unmasked', function () {
        expect((new CEPValidation('78000-000'))->isValid())->toBeTrue();
        expect((new CEPValidation('01310923'))->isValid())->toBeTrue();
    });

    it('rejects wrong length or empty', function () {
        expect((new CEPValidation('78000-00'))->isValid())->toBeFalse();  // 7
        expect((new CEPValidation('013109230'))->isValid())->toBeFalse(); // 9
        expect((new CEPValidation(''))->isValid())->toBeFalse();
    });

    it('ignores non-digits before validating', function () {
        $a = new CEPValidation('  78000-000 ');
        $b = new CEPValidation('78000000');
        expect($a->isValid())->toBeTrue();
        expect($b->isValid())->toBeTrue();
    });

    it('supports whitelist and blacklist short-circuits', function () {
        $w = (new CEPValidation('00000-000'))->allowList(['00000-000']);
        expect($w->isValid())->toBeTrue()
            ->and($w->validateOrFail())->toBeNull();

        $b = (new CEPValidation('78000-000'))->denyList(['78000-000']);
        expect($b->isValid())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class);
    });
});
