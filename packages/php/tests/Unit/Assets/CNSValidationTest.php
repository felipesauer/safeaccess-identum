<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CNS\CNSValidation;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CNSValidation::class, function () {

    it('validates CNS starting with 1/2 (definitive) and 7 (provisional)', function () {
        expect((new CNSValidation('100000000060018'))->isValid())->toBeTrue();
        expect((new CNSValidation('100000000000007'))->isValid())->toBeTrue();
        expect((new CNSValidation('700000000000005'))->isValid())->toBeTrue();
        expect((new CNSValidation(' 1000 0000 0060-018 '))->isValid())->toBeTrue();
        expect((new CNSValidation('7000 0000 0000-005'))->isValid())->toBeTrue();
    });

    it('rejects wrong check logic or start digit', function () {
        expect((new CNSValidation('100000000060019'))->isValid())->toBeFalse();
        expect((new CNSValidation('700000000000004'))->isValid())->toBeFalse();
        expect((new CNSValidation('300000000000000'))->isValid())->toBeFalse();
        expect((new CNSValidation('10000000006001'))->isValid())->toBeFalse();
        expect((new CNSValidation('1000000000600180'))->isValid())->toBeFalse();
    });

    it('whitelist wins and blacklist blocks even if domain logic disagrees', function () {
        $invalid = '300000000000000';
        $doc = (new CNSValidation($invalid))->allowList([$invalid]);
        expect($doc->validateOrFail())->toBeNull();

        $valid = '100000000000007';
        $blk  = (new CNSValidation($valid))->denyList([$valid]);
        expect($blk->isValid())->toBeFalse();
        expect(fn () => $blk->validateOrFail())->toThrow(ValidationException::class, 'cns: denied');
    });

    it('covers the dv==10 branch for 1/2 starters explicitly', function () {
        expect((new CNSValidation('100000000060018'))->isValid())->toBeTrue();
    });

    it('hits dv==11 branch (definitive CNS with "000" + dv=0)', function () {
        expect((new CNSValidation('100000000080000'))->isValid())->toBeTrue();
    });
});
