<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\PIS\PISValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(PISValidation::class, function () {

    it('validates PIS (masked and unmasked) as true', function () {
        expect((new PISValidation('32995061589'))->validate())->toBeTrue();
        expect((new PISValidation('329.9506.158-9'))->validate())->toBeTrue();

        expect((new PISValidation('19121693121'))->validate())->toBeTrue();
        expect((new PISValidation('191.2169.312-1'))->validate())->toBeTrue();
    });

    it('hits the dv==10 and dv==11 edge cases (dv coerced to 0)', function () {
        expect((new PISValidation('40000000000'))->validate())->toBeTrue();
        expect((new PISValidation('76000000000'))->validate())->toBeTrue();
    });

    it('rejects PIS with wrong DV or length or repeated digits', function () {
        expect((new PISValidation('32995061580'))->validate())->toBeFalse();
        expect((new PISValidation('19121693120'))->validate())->toBeFalse();

        expect((new PISValidation('3299506158'))->validate())->toBeFalse();
        expect((new PISValidation('329950615890'))->validate())->toBeFalse();

        expect((new PISValidation('00000000000'))->validate())->toBeFalse();
        expect((new PISValidation('11111111111'))->validate())->toBeFalse();
    });

    it('ignores non-digits before validating', function () {
        $masked  = new PISValidation('329.9506.158-9');
        $noisy   = new PISValidation("  329 9506..158-9 \n\t");
        $clean   = new PISValidation('32995061589');

        expect($masked->validate())->toBeTrue();
        expect($noisy->validate())->toBeTrue();
        expect($clean->validate())->toBeTrue();
    });

    it('supports whitelist and blacklist with validateOrFail', function () {
        $rawInvalid = '12345678901';
        $w = (new PISValidation($rawInvalid))->whitelist([$rawInvalid]);
        expect($w->validateOrFail())->toBeTrue();

        $rawValid = '19121693121';
        $b = (new PISValidation($rawValid))->blacklist([$rawValid]);
        expect($b->validate())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class, 'input invalid');
    });
});
