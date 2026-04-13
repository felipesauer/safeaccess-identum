<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CNH\CNHValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CNHValidation::class, function () {

    it('validates CNH numbers as true (unmasked and with noise)', function () {
        expect((new CNHValidation('22522791508')))->validate()->toBeTrue();
        expect((new CNHValidation('12345678900')))->validate()->toBeTrue();
        expect((new CNHValidation('  225227915-08 ')))->validate()->toBeTrue();
    });

    it('rejects wrong check digits (DV mismatch)', function () {
        expect((new CNHValidation('22522791509')))->validate()->toBeFalse();
        expect((new CNHValidation('92079525000')))->validate()->toBeFalse();
    });

    it('rejects wrong lengths and repeated sequences', function () {
        expect((new CNHValidation('2252279150')))->validate()->toBeFalse();
        expect((new CNHValidation('225227915080')))->validate()->toBeFalse();
        expect((new CNHValidation('00000000000')))->validate()->toBeFalse();
        expect((new CNHValidation('11111111111')))->validate()->toBeFalse();
    });

    it('whitelist short-circuits to valid even if DVs are wrong', function () {
        $raw = '99999999999';
        $doc = (new CNHValidation($raw))->whitelist([$raw]);

        expect($doc->validate())->toBeTrue()
            ->and($doc->validateOrFail())->toBeTrue();
    });

    it('blacklist short-circuits to invalid even if domain is valid', function () {
        $raw = '22522791508';
        $doc = (new CNHValidation($raw))->blacklist([$raw]);

        expect($doc->validate())->toBeFalse();
        expect(fn () => $doc->validateOrFail())->toThrow(ValidationException::class, 'input invalid');
    });

    it('validateOrFail returns true for valid and throws for invalid', function () {
        expect((new CNHValidation('12345678900'))->validateOrFail())->toBeTrue();
        expect(fn () => (new CNHValidation('92079525000'))->validateOrFail())
            ->toThrow(ValidationException::class, 'input invalid');
    });

    it('hits the dv2 adjustment branch (dv1=10→0 and dv2-2<0 → +9)', function () {
        expect((new CNHValidation('10005500000'))->validate())->toBeTrue();
    });
});
