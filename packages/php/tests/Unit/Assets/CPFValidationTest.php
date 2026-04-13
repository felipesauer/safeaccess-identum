<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CPF\CPFValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CPFValidation::class, function () {

    it('validates CPF (masked and unmasked) as true', function () {
        expect((new CPFValidation('864.600.120-24'))->validate())->toBeTrue();
        expect((new CPFValidation('71031347070'))->validate())->toBeTrue();
        expect((new CPFValidation('93011581088'))->validate())->toBeTrue();
        expect((new CPFValidation('745.508.470-69'))->validate())->toBeTrue();
    });

    it('rejects CPF with wrong check digits (DV)', function () {
        expect((new CPFValidation('323.543.123-43'))->validate())->toBeFalse();
        expect((new CPFValidation('98273487634'))->validate())->toBeFalse();
    });

    it('rejects CPF with wrong length', function () {
        expect((new CPFValidation('9999999999'))->validate())->toBeFalse();
        expect((new CPFValidation('123456789012'))->validate())->toBeFalse();
    });

    it('rejects CPF made of repeated digits', function () {
        expect((new CPFValidation('00000000000'))->validate())->toBeFalse();
        expect((new CPFValidation('11111111111'))->validate())->toBeFalse();
        expect((new CPFValidation('222.222.222-22'))->validate())->toBeFalse();
    });

    it('rejects empty or non-sense strings', function () {
        expect((new CPFValidation(''))->validate())->toBeFalse();
        expect((new CPFValidation('   '))->validate())->toBeFalse();
    });

    it('ignores non-digit characters before validating', function () {
        $formatted   = new CPFValidation('864.600.120-24');
        $withNoise   = new CPFValidation("  864 600-120..24 \n\t");
        $digitsOnly  = new CPFValidation('86460012024');

        expect($formatted->validate())->toBeTrue();
        expect($withNoise->validate())->toBeTrue();
        expect($digitsOnly->validate())->toBeTrue();
    });

    it('whitelist() overrides invalid result', function () {
        expect((new CPFValidation('323.543.123-43'))->whitelist(['323.543.123-43'])->validate())->toBeTrue();
    });

    it('blacklist() overrides valid result', function () {
        expect((new CPFValidation('864.600.120-24'))->blacklist(['864.600.120-24'])->validate())->toBeFalse();
    });

    it('validateOrFail() returns true when valid', function () {
        expect((new CPFValidation('864.600.120-24'))->validateOrFail())->toBeTrue();
    });

    it('validateOrFail() throws ValidationException when invalid', function () {
        expect(fn () => (new CPFValidation('323.543.123-43'))->validateOrFail())
            ->toThrow(ValidationException::class, 'input invalid');
    });

    it('validateOrFail() respects whitelist and blacklist', function () {
        expect((new CPFValidation('323.543.123-43'))->whitelist(['323.543.123-43'])->validateOrFail())->toBeTrue();
        expect(fn () => (new CPFValidation('864.600.120-24'))->blacklist(['864.600.120-24'])->validateOrFail())
            ->toThrow(ValidationException::class);
    });
});
