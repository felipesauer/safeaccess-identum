<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CNPJ\CNPJValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CNPJValidation::class, function () {

    it('validates numeric CNPJ (masked and unmasked) as true', function () {
        expect((new CNPJValidation('84.773.274/0001-03'))->validate())->toBeTrue();
        expect((new CNPJValidation('31.605.328/0001-65'))->validate())->toBeTrue();
        expect((new CNPJValidation('52838678000141'))->validate())->toBeTrue();
        expect((new CNPJValidation('40.670.922/0001-20'))->validate())->toBeTrue();
    });

    it('validates alphanumeric CNPJ as true', function () {
        expect((new CNPJValidation('A0000000000032'))->validate())->toBeTrue();
        expect((new CNPJValidation('a0.000.000/0000-32'))->validate())->toBeTrue();
    });

    it('rejects CNPJ with wrong check digits (numeric and alphanumeric)', function () {
        expect((new CNPJValidation('46.543.423/0001-22'))->validate())->toBeFalse();
        expect((new CNPJValidation('09387424300012'))->validate())->toBeFalse();
        expect((new CNPJValidation('A0000000000033'))->validate())->toBeFalse();
    });

    it('rejects CNPJ with wrong length', function () {
        expect((new CNPJValidation('9999999999999'))->validate())->toBeFalse();
        expect((new CNPJValidation('123456789012345'))->validate())->toBeFalse();
        expect((new CNPJValidation(''))->validate())->toBeFalse();
    });

    it('rejects numeric CNPJ made of repeated digits', function () {
        expect((new CNPJValidation('00000000000000'))->validate())->toBeFalse();
        expect((new CNPJValidation('11111111111111'))->validate())->toBeFalse();
        expect((new CNPJValidation('22.222.222/2222-22'))->validate())->toBeFalse();
    });

    it('rejects letters in DV positions (must be digits)', function () {
        expect((new CNPJValidation('A00000000000AA'))->validate())->toBeFalse();
        expect((new CNPJValidation('0000000000000A'))->validate())->toBeFalse();
    });

    it('ignores non-alphanumeric characters before validating', function () {
        $masked = new CNPJValidation('84.773.274/0001-03');
        $noisy  = new CNPJValidation("  84 773.274//0001-03 \n\t");
        $clean  = new CNPJValidation('84773274000103');

        expect($masked->validate())->toBeTrue();
        expect($noisy->validate())->toBeTrue();
        expect($clean->validate())->toBeTrue();
    });

    it('whitelist makes value valid regardless of domain logic', function () {
        $doc = (new CNPJValidation('46.543.423/0001-22'))
            ->whitelist(['46.543.423/0001-22']);

        expect($doc->validate())->toBeTrue();
    });

    it('blacklist makes value invalid regardless of domain logic', function () {
        $doc = (new CNPJValidation('84.773.274/0001-03'))
            ->blacklist(['84.773.274/0001-03']);

        expect($doc->validate())->toBeFalse();
    });

    it('whitelist takes precedence over blacklist when both contain the raw value', function () {
        $raw = '84.773.274/0001-03';
        $doc = (new CNPJValidation($raw))
            ->blacklist([$raw])
            ->whitelist([$raw]);

        expect($doc->validate())->toBeTrue();
    });

    it('validateOrFail() returns true when valid', function () {
        expect((new CNPJValidation('52838678000141'))->validateOrFail())->toBeTrue();
        expect((new CNPJValidation('A0000000000032'))->validateOrFail())->toBeTrue();
    });

    it('validateOrFail() throws when invalid', function () {
        expect(fn () => (new CNPJValidation('09387424300012'))->validateOrFail())
            ->toThrow(ValidationException::class, 'input invalid');
    });

    it('validateOrFail() respects whitelist and blacklist', function () {
        $raw = '09387424300012';
        $w = (new CNPJValidation($raw))->whitelist([$raw]);
        expect($w->validateOrFail())->toBeTrue();

        $raw2 = '84.773.274/0001-03';
        $b = (new CNPJValidation($raw2))->blacklist([$raw2]);
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class);
    });

    it('rejects alphanumeric CNPJ with invalid character in the body (hits val() -1 path)', function () {
        expect((new CNPJValidation('A@0000000000032'))->validate())->toBeFalse();
        expect((new CNPJValidation('AA00#000000032'))->validate())->toBeFalse();
    });
});
