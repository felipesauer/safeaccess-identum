import { describe, it, expect } from 'vitest';
import { CNSValidation } from '../../src/assets/cns/cns-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CNSValidation.name, () => {
    it('validates CNS starting with 1/2 (definitive) and 7 (provisional)', () => {
        expect(new CNSValidation('100000000060018').isValid()).toBe(true);
        expect(new CNSValidation('100000000000007').isValid()).toBe(true);
        expect(new CNSValidation('700000000000005').isValid()).toBe(true);
        expect(new CNSValidation(' 1000 0000 0060-018 ').isValid()).toBe(true);
        expect(new CNSValidation('7000 0000 0000-005').isValid()).toBe(true);
    });

    it('rejects wrong check logic or start digit', () => {
        expect(new CNSValidation('100000000060019').isValid()).toBe(false);
        expect(new CNSValidation('700000000000004').isValid()).toBe(false);
        expect(new CNSValidation('300000000000000').isValid()).toBe(false);
        expect(new CNSValidation('10000000006001').isValid()).toBe(false);
        expect(new CNSValidation('1000000000600180').isValid()).toBe(false);
    });

    it('whitelist wins and blacklist blocks even if domain logic disagrees', () => {
        expect(new CNSValidation('300000000000000').allowList(['300000000000000']).validateOrFail()).toBeUndefined();

        const bl = new CNSValidation('100000000000007').denyList(['100000000000007']);
        expect(bl.isValid()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
        expect(() => bl.validateOrFail()).toThrow('cns: denied');
    });

    it('covers the dv==10 branch for 1/2 starters explicitly', () => {
        expect(new CNSValidation('100000000060018').isValid()).toBe(true);
    });

    it('hits dv==11 branch (definitive CNS with dv=0)', () => {
        expect(new CNSValidation('100000000080000').isValid()).toBe(true);
    });

    it('validates CNS starting with 2 (definitive)', () => {
        expect(new CNSValidation('200000000000003').isValid()).toBe(true);
    });

    it('validates CNS starting with 8 (provisional)', () => {
        expect(new CNSValidation('800000000000001').isValid()).toBe(true);
    });

    it('validates CNS starting with 9 (provisional)', () => {
        expect(new CNSValidation('900000000000008').isValid()).toBe(true);
    });

    it('rejects CNS starting with 4, 5, or 6', () => {
        expect(new CNSValidation('400000000000000').isValid()).toBe(false);
        expect(new CNSValidation('500000000000000').isValid()).toBe(false);
        expect(new CNSValidation('600000000000000').isValid()).toBe(false);
    });

    it('rejects CNS starting with 3 even when sum%11==0 under provisional logic', () => {
        expect(new CNSValidation('300000000000050').isValid()).toBe(false);
    });

    it('validates provisional CNS that would fail definitive logic', () => {
        expect(new CNSValidation('700000000012100').isValid()).toBe(true);
    });

    it('rejects CNS with valid digits but extra length', () => {
        expect(new CNSValidation('1000000000600180').isValid()).toBe(false);
    });
});
