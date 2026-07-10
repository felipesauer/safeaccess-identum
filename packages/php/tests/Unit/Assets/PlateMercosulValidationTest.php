<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\Plate\PlateMercosulValidation;

describe(PlateMercosulValidation::class, function () {
    it('validates Mercosul plate masked and unmasked', function () {
        expect((new PlateMercosulValidation('BRA1A23'))->isValid())->toBeTrue();
        expect((new PlateMercosulValidation('bra-1a23'))->isValid())->toBeTrue();
        expect((new PlateMercosulValidation('ABC3D45'))->isValid())->toBeTrue();
    });

    it('rejects wrong formats and lengths', function () {
        expect((new PlateMercosulValidation('ABC-1234'))->isValid())->toBeFalse();
        expect((new PlateMercosulValidation('AB1CD23'))->isValid())->toBeFalse();
        expect((new PlateMercosulValidation('ABCD123'))->isValid())->toBeFalse();
        expect((new PlateMercosulValidation('BRA1A2'))->isValid())->toBeFalse();
        expect((new PlateMercosulValidation('BRA1A234'))->isValid())->toBeFalse();
    });

    it('supports whitelist and blacklist', function () {
        $w = (new PlateMercosulValidation('ABC1D23'))->allowList(['ABC1D23']);
        expect($w->isValid())->toBeTrue();

        $b = (new PlateMercosulValidation('BRA1A23'))->denyList(['BRA1A23']);
        expect($b->isValid())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(SafeAccess\Identum\Exceptions\ValidationException::class);
    });
});
