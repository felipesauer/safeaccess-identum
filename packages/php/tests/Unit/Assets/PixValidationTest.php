<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\Pix\PixValidation;
use SafeAccess\Identum\Contracts\ReasonCode;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(PixValidation::class, function () {

    it('accepts each of the five key types and reports keyType', function () {
        // List of [key, type] pairs — avoids PHP coercing numeric string keys to int.
        $cases = [
            ['52998224725', 'cpf'],
            ['84773274000103', 'cnpj'],
            ['pix@bcb.gov.br', 'email'],
            ['+5510998765432', 'phone'],
            ['550e8400-e29b-41d4-a716-446655440000', 'evp'],
        ];

        foreach ($cases as [$key, $type]) {
            $result = (new PixValidation($key))->validate();
            expect($result->valid)->toBeTrue()
                ->and($result->meta?->keyType)->toBe($type);
        }
    });

    it('accepts a generic UUID as EVP (BACEN spec is not strict v4)', function () {
        // The BACEN OpenAPI EVP example itself is not a valid v4 UUID.
        expect((new PixValidation('123e4567-e89b-12d3-a456-426655440000'))->isValid())->toBeTrue();
    });

    it('trims surrounding whitespace', function () {
        expect((new PixValidation('  pix@bcb.gov.br  '))->isValid())->toBeTrue();
    });

    it('rejects a CPF/CNPJ key that fails its checksum with bad_check_digit', function () {
        expect((new PixValidation('52998224724'))->validate()->reason)->toBe(ReasonCode::BadCheckDigit);
        expect((new PixValidation('84773274000104'))->validate()->reason)->toBe(ReasonCode::BadCheckDigit);
    });

    it('rejects malformed keys with invalid_format', function () {
        foreach (['notanemail@', '+55', 'abc', '', '999.999.999-99', 'not-a-uuid-value'] as $bad) {
            expect((new PixValidation($bad))->validate()->reason)->toBe(ReasonCode::InvalidFormat);
        }
    });

    it('rejects an e-mail longer than the 77-char DICT limit', function () {
        $long = str_repeat('a', 70) . '@x.com'; // 76
        expect((new PixValidation($long))->isValid())->toBeTrue();
        $tooLong = str_repeat('a', 72) . '@x.com'; // 78
        expect((new PixValidation($tooLong))->validate()->reason)->toBe(ReasonCode::InvalidFormat);
    });

    it('accepts E.164 up to 15 digits and rejects 16 (phone key)', function () {
        expect((new PixValidation('+' . str_repeat('1', 15)))->validate()->meta?->keyType)->toBe('phone'); // 15
        expect((new PixValidation('+' . str_repeat('1', 16)))->validate()->reason)->toBe(ReasonCode::InvalidFormat); // 16
    });

    it('meta.keyType is null for an invalid key', function () {
        expect((new PixValidation('abc'))->validate()->meta)->toBeNull();
    });

    it('validateOrFail() throws with the pix document prefix', function () {
        expect(fn () => (new PixValidation('abc'))->validateOrFail())
            ->toThrow(ValidationException::class, 'pix: invalid_format');
    });
});
