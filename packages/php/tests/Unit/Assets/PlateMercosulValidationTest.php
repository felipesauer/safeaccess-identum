<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\Plate\PlateMercosulValidation;

describe(PlateMercosulValidation::class, function () {
    it('validates Mercosul plate masked and unmasked', function () {
        expect((new PlateMercosulValidation('BRA1A23'))->validate())->toBeTrue();
        expect((new PlateMercosulValidation('bra-1a23'))->validate())->toBeTrue();
        expect((new PlateMercosulValidation('ABC3D45'))->validate())->toBeTrue();
    });

    it('rejects wrong formats and lengths', function () {
        expect((new PlateMercosulValidation('ABC-1234'))->validate())->toBeFalse();
        expect((new PlateMercosulValidation('AB1CD23'))->validate())->toBeFalse();
        expect((new PlateMercosulValidation('ABCD123'))->validate())->toBeFalse();
        expect((new PlateMercosulValidation('BRA1A2'))->validate())->toBeFalse();
        expect((new PlateMercosulValidation('BRA1A234'))->validate())->toBeFalse();
    });

    it('supports whitelist and blacklist', function () {
        $w = (new PlateMercosulValidation('ABC1D23'))->whitelist(['ABC1D23']);
        expect($w->validate())->toBeTrue();

        $b = (new PlateMercosulValidation('BRA1A23'))->blacklist(['BRA1A23']);
        expect($b->validate())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(SafeAccess\Identum\Exceptions\ValidationException::class);
    });
});
