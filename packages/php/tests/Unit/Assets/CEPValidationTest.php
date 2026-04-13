<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CEP\CEPValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CEPValidation::class, function () {

    it('accepts CEP masked and unmasked', function () {
        expect((new CEPValidation('78000-000'))->validate())->toBeTrue();
        expect((new CEPValidation('01310923'))->validate())->toBeTrue();
    });

    it('rejects wrong length or empty', function () {
        expect((new CEPValidation('78000-00'))->validate())->toBeFalse();  // 7
        expect((new CEPValidation('013109230'))->validate())->toBeFalse(); // 9
        expect((new CEPValidation(''))->validate())->toBeFalse();
    });

    it('ignores non-digits before validating', function () {
        $a = new CEPValidation('  78000-000 ');
        $b = new CEPValidation('78000000');
        expect($a->validate())->toBeTrue();
        expect($b->validate())->toBeTrue();
    });

    it('supports whitelist and blacklist short-circuits', function () {
        $w = (new CEPValidation('00000-000'))->whitelist(['00000-000']);
        expect($w->validate())->toBeTrue()
            ->and($w->validateOrFail())->toBeTrue();

        $b = (new CEPValidation('78000-000'))->blacklist(['78000-000']);
        expect($b->validate())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class);
    });
});
