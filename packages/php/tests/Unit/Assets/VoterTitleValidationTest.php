<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\Voter\VoterTitleValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(VoterTitleValidation::class, function () {

    it('validates Voter Title (masked and unmasked) as true', function () {
        expect((new VoterTitleValidation('123456781295'))->isValid())->toBeTrue();
        expect((new VoterTitleValidation('314159261260'))->isValid())->toBeTrue();

        expect((new VoterTitleValidation('  1234 5678 12-95 '))->isValid())->toBeTrue();
        expect((new VoterTitleValidation('3141.5926.12-60'))->isValid())->toBeTrue();
    });

    it('rejects wrong check digits (DV mismatch)', function () {
        expect((new VoterTitleValidation('123456781294'))->isValid())->toBeFalse();
        expect((new VoterTitleValidation('314159261261'))->isValid())->toBeFalse();
    });

    it('rejects wrong length and repeated sequences', function () {
        expect((new VoterTitleValidation('12345678129'))->isValid())->toBeFalse();
        expect((new VoterTitleValidation('1234567812950'))->isValid())->toBeFalse();
        expect((new VoterTitleValidation('000000000000'))->isValid())->toBeFalse();
        expect((new VoterTitleValidation('111111111111'))->isValid())->toBeFalse();
    });

    it('whitelist short-circuits to valid and blacklist to invalid', function () {
        $rawInvalid = '123456781294';
        $w = (new VoterTitleValidation($rawInvalid))->allowList([$rawInvalid]);
        expect($w->validateOrFail())->toBeNull();

        $rawValid = '123456781295';
        $b = (new VoterTitleValidation($rawValid))->denyList([$rawValid]);
        expect($b->isValid())->toBeFalse();
        expect(fn () => $b->validateOrFail())->toThrow(ValidationException::class, 'voter-title: denied');
    });

    it('hits the dv==10 -> 0 edge in dv1 and dv2', function () {
        expect((new VoterTitleValidation('000000060400'))->isValid())->toBeTrue();
    });
});
