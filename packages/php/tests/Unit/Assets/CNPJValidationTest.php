<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CNPJ\CNPJValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CNPJValidation::class, function () {

    it('validates numeric CNPJ (masked and unmasked) as true', function () {
        expect((new CNPJValidation('84.773.274/0001-03'))->isValid())->toBeTrue();
        expect((new CNPJValidation('31.605.328/0001-65'))->isValid())->toBeTrue();
        expect((new CNPJValidation('52838678000141'))->isValid())->toBeTrue();
        expect((new CNPJValidation('40.670.922/0001-20'))->isValid())->toBeTrue();
    });

    it('validates alphanumeric CNPJ as true', function () {
        expect((new CNPJValidation('A0000000000032'))->isValid())->toBeTrue();
        expect((new CNPJValidation('a0.000.000/0000-32'))->isValid())->toBeTrue();
    });

    it('rejects CNPJ with wrong check digits (numeric and alphanumeric)', function () {
        expect((new CNPJValidation('46.543.423/0001-22'))->isValid())->toBeFalse();
        expect((new CNPJValidation('09387424300012'))->isValid())->toBeFalse();
        expect((new CNPJValidation('A0000000000033'))->isValid())->toBeFalse();
    });

    it('rejects CNPJ with wrong length', function () {
        expect((new CNPJValidation('9999999999999'))->isValid())->toBeFalse();
        expect((new CNPJValidation('123456789012345'))->isValid())->toBeFalse();
        expect((new CNPJValidation(''))->isValid())->toBeFalse();
    });

    it('rejects numeric CNPJ made of repeated digits', function () {
        expect((new CNPJValidation('00000000000000'))->isValid())->toBeFalse();
        expect((new CNPJValidation('11111111111111'))->isValid())->toBeFalse();
        expect((new CNPJValidation('22.222.222/2222-22'))->isValid())->toBeFalse();
    });

    it('rejects letters in DV positions (must be digits)', function () {
        expect((new CNPJValidation('A00000000000AA'))->isValid())->toBeFalse();
        expect((new CNPJValidation('0000000000000A'))->isValid())->toBeFalse();
    });

    it('ignores non-alphanumeric characters before validating', function () {
        $masked = new CNPJValidation('84.773.274/0001-03');
        $noisy  = new CNPJValidation("  84 773.274//0001-03 \n\t");
        $clean  = new CNPJValidation('84773274000103');

        expect($masked->isValid())->toBeTrue();
        expect($noisy->isValid())->toBeTrue();
        expect($clean->isValid())->toBeTrue();
    });

    it('whitelist makes value valid regardless of domain logic', function () {
        $doc = (new CNPJValidation('46.543.423/0001-22'))
            ->allowList(['46.543.423/0001-22']);

        expect($doc->isValid())->toBeTrue();
    });

    it('blacklist makes value invalid regardless of domain logic', function () {
        $doc = (new CNPJValidation('84.773.274/0001-03'))
            ->denyList(['84.773.274/0001-03']);

        expect($doc->isValid())->toBeFalse();
    });

    it('whitelist takes precedence over blacklist when both contain the raw value', function () {
        $raw = '84.773.274/0001-03';
        $doc = (new CNPJValidation($raw))
            ->denyList([$raw])
            ->allowList([$raw]);

        expect($doc->isValid())->toBeTrue();
    });

    it('validateOrFail() returns true when valid', function () {
        expect((new CNPJValidation('52838678000141'))->validateOrFail())->toBeNull();
        expect((new CNPJValidation('A0000000000032'))->validateOrFail())->toBeNull();
    });

    it('validateOrFail() throws when invalid', function () {
        expect(fn () => (new CNPJValidation('09387424300012'))->validateOrFail())
            ->toThrow(ValidationException::class, 'cnpj: bad_check_digit');
    });

    it('validateOrFail() respects whitelist and blacklist', function () {
        $raw = '09387424300012';
        $w = (new CNPJValidation($raw))->allowList([$raw]);
        expect($w->validateOrFail())->toBeNull();

        $raw2 = '84.773.274/0001-03';
        $b = (new CNPJValidation($raw2))->denyList([$raw2]);
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class);
    });

    it('rejects alphanumeric CNPJ with invalid character in the body (hits val() -1 path)', function () {
        expect((new CNPJValidation('A@0000000000032'))->isValid())->toBeFalse();
        expect((new CNPJValidation('AA00#000000032'))->isValid())->toBeFalse();
    });
});
