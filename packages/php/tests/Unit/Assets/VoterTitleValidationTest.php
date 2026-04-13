<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\Voter\VoterTitleValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(VoterTitleValidation::class, function () {

    it('validates Voter Title (masked and unmasked) as true', function () {
        expect((new VoterTitleValidation('123456781295'))->validate())->toBeTrue();
        expect((new VoterTitleValidation('314159261260'))->validate())->toBeTrue();

        expect((new VoterTitleValidation('  1234 5678 12-95 '))->validate())->toBeTrue();
        expect((new VoterTitleValidation('3141.5926.12-60'))->validate())->toBeTrue();
    });

    it('rejects wrong check digits (DV mismatch)', function () {
        expect((new VoterTitleValidation('123456781294'))->validate())->toBeFalse();
        expect((new VoterTitleValidation('314159261261'))->validate())->toBeFalse();
    });

    it('rejects wrong length and repeated sequences', function () {
        expect((new VoterTitleValidation('12345678129'))->validate())->toBeFalse();
        expect((new VoterTitleValidation('1234567812950'))->validate())->toBeFalse();
        expect((new VoterTitleValidation('000000000000'))->validate())->toBeFalse();
        expect((new VoterTitleValidation('111111111111'))->validate())->toBeFalse();
    });

    it('whitelist short-circuits to valid and blacklist to invalid', function () {
        $rawInvalid = '123456781294';
        $w = (new VoterTitleValidation($rawInvalid))->whitelist([$rawInvalid]);
        expect($w->validateOrFail())->toBeTrue();

        $rawValid = '123456781295';
        $b = (new VoterTitleValidation($rawValid))->blacklist([$rawValid]);
        expect($b->validate())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class, 'input invalid');
    });

    it('hits the dv==10 -> 0 edge in dv1 and dv2', function () {
        expect((new VoterTitleValidation('000000060400'))->validate())->toBeTrue();
    });
});
