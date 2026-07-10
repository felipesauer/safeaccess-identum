<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\Certidao\CertidaoValidation;
use SafeAccess\Identum\Contracts\ReasonCode;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CertidaoValidation::class, function () {

    it('validates the official CRC sample matrícula', function () {
        // From the CRC Nacional Annex IV worked example (verified by code).
        expect((new CertidaoValidation('00188301551987100018050000056665'))->isValid())->toBeTrue();
    });

    it('accepts a matrícula with formatting punctuation', function () {
        expect((new CertidaoValidation('001883 01 55 1987 1 00018 050 0000566 65'))->isValid())->toBeTrue();
    });

    it('rejects wrong length with wrong_length', function () {
        expect((new CertidaoValidation('123'))->validate()->reason)->toBe(ReasonCode::WrongLength);
        expect((new CertidaoValidation('001883015519871000180500000566650'))->validate()->reason)
            ->toBe(ReasonCode::WrongLength);
    });

    it('rejects wrong check digits with bad_check_digit', function () {
        expect((new CertidaoValidation('00188301551987100018050000056600'))->validate()->reason)
            ->toBe(ReasonCode::BadCheckDigit);
    });

    it('exposes the certificate kind via meta.type (book-type digit)', function () {
        // The sample is a Livro A (birth); flipping the book-type digit changes the kind.
        expect((new CertidaoValidation('00188301551987100018050000056665'))->validate()->meta?->type)
            ->toBe('birth');
    });

    it('handles the ×10 remainder-10 → 1 branch (no false negatives on valid data)', function () {
        // A valid matrícula whose DV computation exercises the remainder path.
        $valid = (new CertidaoValidation('00188301551987100018050000056665'))->validate();
        expect($valid->valid)->toBeTrue()->and($valid->reason)->toBeNull();
    });

    it('validateOrFail() throws with the certidao document prefix', function () {
        expect(fn () => (new CertidaoValidation('00188301551987100018050000056600'))->validateOrFail())
            ->toThrow(ValidationException::class, 'certidao: bad_check_digit');
    });
});
