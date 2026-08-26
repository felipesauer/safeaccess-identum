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
        expect(() => new CartaoValidation('4111111111111112').validateOrFail()).toThrow(
            ValidationException,
        );
        expect(() => new CartaoValidation('4111111111111112').validateOrFail()).toThrow(
            'cartao: bad_check_digit',
        );
    });

    /**
     * Completes a BIN into a 16-digit PAN: zero-pads to 15 digits and appends the
     * Luhn check digit. Written independently of the validator, so asserting
     * `valid === true` below also checks the validator's own Luhn against it.
     */
    const pan = (bin: string): string => {
        const body = bin.padEnd(15, '0');
        const sum = [...body].reverse().reduce((acc, ch, i) => {
            const d = i % 2 === 0 ? Number(ch) * 2 : Number(ch);
            return acc + (d > 9 ? d - 9 : d);
        }, 0);
        return body + String((10 - (sum % 10)) % 10);
    };

    /**
     * One BIN per boundary of every range in BRAND_RULES — first and last value of
     * each character class. A narrowed or negated class stops matching its own
     * boundaries, so these pin the ranges down.
     */
    it.each([
        // Elo — discrete BINs
        ['401178', 'elo'],
        ['401179', 'elo'],
        ['431274', 'elo'],
        ['438935', 'elo'],
        ['451416', 'elo'],
        ['457393', 'elo'],
        ['457631', 'elo'],
        ['457632', 'elo'],
        ['504175', 'elo'],
        ['627780', 'elo'],
        ['636297', 'elo'],
        ['636368', 'elo'],
        // Elo — 5067xx / 50677x
        ['506699', 'elo'],
        ['506700', 'elo'],
        ['506769', 'elo'],
        ['506770', 'elo'],
        ['506778', 'elo'],
        // Elo — 509xxx
        ['509000', 'elo'],
        ['509999', 'elo'],
        // Elo — 6500xx
        ['650031', 'elo'],
        ['650033', 'elo'],
        ['650035', 'elo'],
        ['650039', 'elo'],
        ['650040', 'elo'],
        ['650049', 'elo'],
        ['650050', 'elo'],
        ['650059', 'elo'],
        // Elo — 6504xx / 6505xx
        ['650405', 'elo'],
        ['650409', 'elo'],
        ['650410', 'elo'],
        ['650439', 'elo'],
        ['650485', 'elo'],
        ['650489', 'elo'],
        ['650490', 'elo'],
        ['650499', 'elo'],
        ['650500', 'elo'],
        ['650529', 'elo'],
        ['650530', 'elo'],
        ['650538', 'elo'],
        ['650541', 'elo'],
        ['650549', 'elo'],
        ['650550', 'elo'],
        ['650589', 'elo'],
        ['650590', 'elo'],
        ['650598', 'elo'],
        // Elo — 6507xx / 6508xx
        ['650700', 'elo'],
        ['650769', 'elo'],
        ['650770', 'elo'],
        ['650778', 'elo'],
        ['650810', 'elo'],
        ['650819', 'elo'],
        ['650820', 'elo'],
        ['650889', 'elo'],
        // Elo — 6516xx / 655xxx
        ['651652', 'elo'],
        ['651659', 'elo'],
        ['651660', 'elo'],
        ['651679', 'elo'],
        ['655000', 'elo'],
        ['655009', 'elo'],
        ['655010', 'elo'],
        ['655049', 'elo'],
        ['655050', 'elo'],
        ['655058', 'elo'],
        // Hipercard
        ['384100', 'hipercard'],
        ['384140', 'hipercard'],
        ['384160', 'hipercard'],
        ['606282', 'hipercard'],
        ['637095', 'hipercard'],
        ['637568', 'hipercard'],
        ['637599', 'hipercard'],
        ['637609', 'hipercard'],
        ['637612', 'hipercard'],
        // Amex
        ['340000', 'amex'],
        ['370000', 'amex'],
        // Visa
        ['400000', 'visa'],
        ['499999', 'visa'],
        // Mastercard — 51-55 and the 2-series
        ['510000', 'mastercard'],
        ['550000', 'mastercard'],
        ['222100', 'mastercard'],
        ['222900', 'mastercard'],
        ['223000', 'mastercard'],
        ['229900', 'mastercard'],
        ['230000', 'mastercard'],
        ['269900', 'mastercard'],
        ['270000', 'mastercard'],
        ['271900', 'mastercard'],
        ['272000', 'mastercard'],
    ])('maps BIN %s to brand %s', (bin, brand) => {
        const result = new CartaoValidation(pan(bin)).validate();
        expect(result.valid).toBe(true);
        expect(result.meta?.brand).toBe(brand);
    });

    /**
     * Every BIN rule is anchored at the start. A BIN that merely appears further
     * along the PAN must not decide the brand.
     */
    it.each([
        ['4401178', 'visa'], // elo BIN at offset 1
        ['4627780', 'visa'],
        ['4506699', 'visa'],
        ['4509000', 'visa'],
        ['4650031', 'visa'],
        ['4650405', 'visa'],
        ['4650541', 'visa'],
        ['4651652', 'visa'],
        ['4384100', 'visa'], // hipercard BIN at offset 1
        ['4340000', 'visa'], // amex prefix at offset 1
    ])('ignores the BIN %s when it is not at the start (expects %s)', (prefix, brand) => {
        expect(new CartaoValidation(pan(prefix)).validate().meta?.brand).toBe(brand);
    });

    it('leaves the brand null when a mastercard range appears mid-PAN', () => {
        // Discover BIN: unmapped. The embedded "51" must not make it a mastercard.
        const result = new CartaoValidation(pan('6011510')).validate();
        expect(result.valid).toBe(true);
        expect(result.meta?.brand).toBeNull();
    });

    it('accepts the shortest and longest ISO/IEC 7812 lengths', () => {
        expect(new CartaoValidation('4111111111111111111').validate().reason).toBe(
            'bad_check_digit',
        ); // 19, Luhn fails
        expect(new CartaoValidation('4111111111111111').isValid()).toBe(true); // 16
        expect(new CartaoValidation('12345674').isValid()).toBe(true); // 8, Luhn-valid
        expect(new CartaoValidation('123456740').validate().reason).toBe('bad_check_digit'); // 9
    });
});
