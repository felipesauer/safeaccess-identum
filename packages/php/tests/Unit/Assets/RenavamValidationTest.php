<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\RENAVAM\RenavamValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(RenavamValidation::class, function () {

    it('validates RENAVAM (masked and unmasked) as true', function () {
        expect((new RenavamValidation('60390908553'))->validate())->toBeTrue();
        expect((new RenavamValidation('34118026873'))->validate())->toBeTrue();
        expect((new RenavamValidation('44666210669'))->validate())->toBeTrue();

        expect((new RenavamValidation(' 6039 0908-553 '))->validate())->toBeTrue();
        expect((new RenavamValidation('34.118.026-873'))->validate())->toBeTrue();
    });

    it('rejects wrong check digit (DV mismatch)', function () {
        expect((new RenavamValidation('60390908550'))->validate())->toBeFalse();
        expect((new RenavamValidation('34118026870'))->validate())->toBeFalse();
    });

    it('rejects wrong length and repeated sequences', function () {
        expect((new RenavamValidation('6039090855'))->validate())->toBeFalse();
        expect((new RenavamValidation('603909085530'))->validate())->toBeFalse();
        expect((new RenavamValidation('00000000000'))->validate())->toBeFalse();
        expect((new RenavamValidation('11111111111'))->validate())->toBeFalse();
    });

    it('ignores non-digit characters before validating', function () {
        $masked = new RenavamValidation('341.180.268-73');
        $noisy  = new RenavamValidation("  341 180..268-73 \n\t");
        $clean  = new RenavamValidation('34118026873');

        expect($masked->validate())->toBeTrue();
        expect($noisy->validate())->toBeTrue();
        expect($clean->validate())->toBeTrue();
    });

    it('supports whitelist and blacklist with validateOrFail', function () {
        $rawInvalid = '12345678901';
        $w = (new RenavamValidation($rawInvalid))->whitelist([$rawInvalid]);
        expect($w->validateOrFail())->toBeTrue();

        $rawValid = '60390908553';
        $b = (new RenavamValidation($rawValid))->blacklist([$rawValid]);
        expect($b->validate())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class, 'input invalid');
    });

    it('hits dv>=10 branch and coerces dv to 0', function () {
        expect((new RenavamValidation('00100000010'))->validate())->toBeTrue();
    });
});
