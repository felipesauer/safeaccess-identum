import { describe, it, expect } from 'vitest';
import { CartaoValidation } from '../../src/assets/cartao/cartao-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CartaoValidation.name, () => {
    it('accepts Luhn-valid numbers (various lengths)', () => {
        expect(new CartaoValidation('4111111111111111').isValid()).toBe(true);
        expect(new CartaoValidation('5555555555554444').isValid()).toBe(true);
        expect(new CartaoValidation('378282246310005').isValid()).toBe(true); // Amex, 15
        expect(new CartaoValidation('79927398713').isValid()).toBe(true); // classic
    });

    it('ignores spaces and dashes before validating', () => {
        expect(new CartaoValidation('4111-1111-1111-1111').isValid()).toBe(true);
        expect(new CartaoValidation('4111 1111 1111 1111').isValid()).toBe(true);
    });

    it('rejects a failed Luhn check with bad_check_digit', () => {
        expect(new CartaoValidation('4111111111111112').validate().reason).toBe('bad_check_digit');
    });

    it('rejects out-of-range lengths with wrong_length', () => {
        expect(new CartaoValidation('1234567').validate().reason).toBe('wrong_length'); // 7
        expect(new CartaoValidation('12345678901234567890').validate().reason).toBe('wrong_length'); // 20
    });

    it('detects the brand via BIN (best-effort meta)', () => {
        expect(new CartaoValidation('4111111111111111').validate().meta?.brand).toBe('visa');
        expect(new CartaoValidation('5555555555554444').validate().meta?.brand).toBe('mastercard');
        expect(new CartaoValidation('2223003122003222').validate().meta?.brand).toBe('mastercard'); // 2-series
        expect(new CartaoValidation('378282246310005').validate().meta?.brand).toBe('amex');
        expect(new CartaoValidation('6362970000457013').validate().meta?.brand).toBe('elo');
        expect(new CartaoValidation('6062825624254001').validate().meta?.brand).toBe('hipercard');
    });

    it('leaves brand null for a valid but unmapped BIN (e.g. Discover)', () => {
        const result = new CartaoValidation('6011111111111117').validate();
        expect(result.valid).toBe(true);
        expect(result.meta?.brand).toBeNull();
    });

    it('rejects single-digit sequences with known_invalid (pass Luhn but not real PANs)', () => {
        expect(new CartaoValidation('0000000000000000').validate().reason).toBe('known_invalid');
        expect(new CartaoValidation('00000000').validate().reason).toBe('known_invalid');
    });

    it('validateOrFail() throws with the cartao document prefix', () => {
        expect(() => new CartaoValidation('4111111111111112').validateOrFail()).toThrow(ValidationException);
        expect(() => new CartaoValidation('4111111111111112').validateOrFail()).toThrow('cartao: bad_check_digit');
    });
});
