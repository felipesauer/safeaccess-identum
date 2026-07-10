<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\Cartao\CartaoValidation;
use SafeAccess\Identum\Contracts\ReasonCode;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CartaoValidation::class, function () {

    it('accepts Luhn-valid numbers (various lengths)', function () {
        expect((new CartaoValidation('4111111111111111'))->isValid())->toBeTrue();
        expect((new CartaoValidation('5555555555554444'))->isValid())->toBeTrue();
        expect((new CartaoValidation('378282246310005'))->isValid())->toBeTrue();  // Amex, 15
        expect((new CartaoValidation('79927398713'))->isValid())->toBeTrue();      // classic
    });

    it('ignores spaces and dashes before validating', function () {
        expect((new CartaoValidation('4111-1111-1111-1111'))->isValid())->toBeTrue();
        expect((new CartaoValidation('4111 1111 1111 1111'))->isValid())->toBeTrue();
    });

    it('rejects a failed Luhn check with bad_check_digit', function () {
        expect((new CartaoValidation('4111111111111112'))->validate()->reason)->toBe(ReasonCode::BadCheckDigit);
    });

    it('rejects out-of-range lengths with wrong_length', function () {
        expect((new CartaoValidation('1234567'))->validate()->reason)->toBe(ReasonCode::WrongLength);       // 7
        expect((new CartaoValidation('12345678901234567890'))->validate()->reason)->toBe(ReasonCode::WrongLength); // 20
    });

    it('detects the brand via BIN (best-effort meta)', function () {
        expect((new CartaoValidation('4111111111111111'))->validate()->meta?->brand)->toBe('visa');
        expect((new CartaoValidation('5555555555554444'))->validate()->meta?->brand)->toBe('mastercard');
        expect((new CartaoValidation('2223003122003222'))->validate()->meta?->brand)->toBe('mastercard'); // 2-series
        expect((new CartaoValidation('378282246310005'))->validate()->meta?->brand)->toBe('amex');
        expect((new CartaoValidation('6362970000457013'))->validate()->meta?->brand)->toBe('elo');
        expect((new CartaoValidation('6062825624254001'))->validate()->meta?->brand)->toBe('hipercard');
    });

    it('leaves brand null for a valid but unmapped BIN (e.g. Discover)', function () {
        $result = (new CartaoValidation('6011111111111117'))->validate();
        expect($result->valid)->toBeTrue()
            ->and($result->meta?->brand)->toBeNull();
    });

    it('validateOrFail() throws with the cartao document prefix', function () {
        expect(fn () => (new CartaoValidation('4111111111111112'))->validateOrFail())
            ->toThrow(ValidationException::class, 'cartao: bad_check_digit');
    });

    it('rejects single-digit sequences with known_invalid (pass Luhn but not real PANs)', function () {
        expect((new CartaoValidation('0000000000000000'))->validate()->reason)->toBe(ReasonCode::KnownInvalid);
        expect((new CartaoValidation('00000000'))->validate()->reason)->toBe(ReasonCode::KnownInvalid);
    });
});
