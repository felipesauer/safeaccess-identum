<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\RENAVAM\RenavamValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(RenavamValidation::class, function () {

    it('validates RENAVAM (masked and unmasked) as true', function () {
        expect((new RenavamValidation('60390908553'))->isValid())->toBeTrue();
        expect((new RenavamValidation('34118026873'))->isValid())->toBeTrue();
        expect((new RenavamValidation('44666210669'))->isValid())->toBeTrue();

        expect((new RenavamValidation(' 6039 0908-553 '))->isValid())->toBeTrue();
        expect((new RenavamValidation('34.118.026-873'))->isValid())->toBeTrue();
    });

    it('rejects wrong check digit (DV mismatch)', function () {
        expect((new RenavamValidation('60390908550'))->isValid())->toBeFalse();
        expect((new RenavamValidation('34118026870'))->isValid())->toBeFalse();
    });

    it('rejects wrong length and repeated sequences', function () {
        expect((new RenavamValidation('6039090855'))->isValid())->toBeFalse();
        expect((new RenavamValidation('603909085530'))->isValid())->toBeFalse();
        expect((new RenavamValidation('00000000000'))->isValid())->toBeFalse();
        expect((new RenavamValidation('11111111111'))->isValid())->toBeFalse();
    });

    it('ignores non-digit characters before validating', function () {
        $masked = new RenavamValidation('341.180.268-73');
        $noisy  = new RenavamValidation("  341 180..268-73 \n\t");
        $clean  = new RenavamValidation('34118026873');

        expect($masked->isValid())->toBeTrue();
        expect($noisy->isValid())->toBeTrue();
        expect($clean->isValid())->toBeTrue();
    });

    it('supports whitelist and blacklist with validateOrFail', function () {
        $rawInvalid = '12345678901';
        $w = (new RenavamValidation($rawInvalid))->allowList([$rawInvalid]);
        expect($w->validateOrFail())->toBeNull();

        $rawValid = '60390908553';
        $b = (new RenavamValidation($rawValid))->denyList([$rawValid]);
        expect($b->isValid())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class, 'renavam: denied');
    });

    it('hits dv>=10 branch and coerces dv to 0', function () {
        expect((new RenavamValidation('00100000010'))->isValid())->toBeTrue();
    });
});
