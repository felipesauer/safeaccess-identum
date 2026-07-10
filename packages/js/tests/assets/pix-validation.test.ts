import { describe, it, expect } from 'vitest';
import { PixValidation } from '../../src/assets/pix/pix-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(PixValidation.name, () => {
    it('accepts each of the five key types and reports keyType', () => {
        const cases: Array<[string, string]> = [
            ['52998224725', 'cpf'],
            ['84773274000103', 'cnpj'],
            ['pix@bcb.gov.br', 'email'],
            ['+5510998765432', 'phone'],
            ['550e8400-e29b-41d4-a716-446655440000', 'evp'],
        ];

        for (const [key, type] of cases) {
            const result = new PixValidation(key).validate();
            expect(result.valid).toBe(true);
            expect(result.meta?.keyType).toBe(type);
        }
    });

    it('accepts a generic UUID as EVP (BACEN spec is not strict v4)', () => {
        expect(new PixValidation('123e4567-e89b-12d3-a456-426655440000').isValid()).toBe(true);
    });

    it('trims surrounding whitespace', () => {
        expect(new PixValidation('  pix@bcb.gov.br  ').isValid()).toBe(true);
    });

    it('rejects a CPF/CNPJ key that fails its checksum with bad_check_digit', () => {
        expect(new PixValidation('52998224724').validate().reason).toBe('bad_check_digit');
        expect(new PixValidation('84773274000104').validate().reason).toBe('bad_check_digit');
    });

    it('rejects malformed keys with invalid_format', () => {
        for (const bad of ['notanemail@', '+55', 'abc', '', '999.999.999-99', 'not-a-uuid-value']) {
            expect(new PixValidation(bad).validate().reason).toBe('invalid_format');
        }
    });

    it('rejects an e-mail longer than the 77-char DICT limit', () => {
        expect(new PixValidation('a'.repeat(70) + '@x.com').isValid()).toBe(true); // 76
        expect(new PixValidation('a'.repeat(72) + '@x.com').validate().reason).toBe('invalid_format'); // 78
    });

    it('accepts E.164 up to 15 digits and rejects 16 (phone key)', () => {
        expect(new PixValidation('+' + '1'.repeat(15)).validate().meta?.keyType).toBe('phone'); // 15 digits
        expect(new PixValidation('+' + '1'.repeat(16)).validate().reason).toBe('invalid_format'); // 16 digits
    });

    it('meta is null for an invalid key', () => {
        expect(new PixValidation('abc').validate().meta).toBeNull();
    });

    it('validateOrFail() throws with the pix document prefix', () => {
        expect(() => new PixValidation('abc').validateOrFail()).toThrow(ValidationException);
        expect(() => new PixValidation('abc').validateOrFail()).toThrow('pix: invalid_format');
    });
});
