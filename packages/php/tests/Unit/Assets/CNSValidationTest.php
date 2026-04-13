<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CNS\CNSValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CNSValidation::class, function () {

    it('validates CNS starting with 1/2 (definitive) and 7 (provisional)', function () {
        expect((new CNSValidation('100000000060018'))->validate())->toBeTrue();
        expect((new CNSValidation('100000000000007'))->validate())->toBeTrue();
        expect((new CNSValidation('700000000000005'))->validate())->toBeTrue();
        expect((new CNSValidation(' 1000 0000 0060-018 '))->validate())->toBeTrue();
        expect((new CNSValidation('7000 0000 0000-005'))->validate())->toBeTrue();
    });

    it('rejects wrong check logic or start digit', function () {
        expect((new CNSValidation('100000000060019'))->validate())->toBeFalse();
        expect((new CNSValidation('700000000000004'))->validate())->toBeFalse();
        expect((new CNSValidation('300000000000000'))->validate())->toBeFalse();
        expect((new CNSValidation('10000000006001'))->validate())->toBeFalse();
        expect((new CNSValidation('1000000000600180'))->validate())->toBeFalse();
    });

    it('whitelist wins and blacklist blocks even if domain logic disagrees', function () {
        $invalid = '300000000000000';
        $doc = (new CNSValidation($invalid))->whitelist([$invalid]);
        expect($doc->validateOrFail())->toBeTrue();

        $valid = '100000000000007';
        $blk  = (new CNSValidation($valid))->blacklist([$valid]);
        expect($blk->validate())->toBeFalse();
        expect(fn () => $blk->validateOrFail())->toThrow(ValidationException::class, 'input invalid');
    });

    it('covers the dv==10 branch for 1/2 starters explicitly', function () {
        expect((new CNSValidation('100000000060018'))->validate())->toBeTrue();
    });

    it('hits dv==11 branch (definitive CNS with "000" + dv=0)', function () {
        expect((new CNSValidation('100000000080000'))->validate())->toBeTrue();
    });
});
