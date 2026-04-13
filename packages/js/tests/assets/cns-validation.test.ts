import { describe, it, expect } from 'vitest';
import { CNSValidation } from '../../src/assets/cns/cns-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CNSValidation.name, () => {
    it('validates CNS starting with 1/2 (definitive) and 7 (provisional)', () => {
        expect(new CNSValidation('100000000060018').validate()).toBe(true);
        expect(new CNSValidation('100000000000007').validate()).toBe(true);
        expect(new CNSValidation('700000000000005').validate()).toBe(true);
        expect(new CNSValidation(' 1000 0000 0060-018 ').validate()).toBe(true);
        expect(new CNSValidation('7000 0000 0000-005').validate()).toBe(true);
    });

    it('rejects wrong check logic or start digit', () => {
        expect(new CNSValidation('100000000060019').validate()).toBe(false);
        expect(new CNSValidation('700000000000004').validate()).toBe(false);
        expect(new CNSValidation('300000000000000').validate()).toBe(false);
        expect(new CNSValidation('10000000006001').validate()).toBe(false);
        expect(new CNSValidation('1000000000600180').validate()).toBe(false);
    });

    it('whitelist wins and blacklist blocks even if domain logic disagrees', () => {
        expect(new CNSValidation('300000000000000').whitelist(['300000000000000']).validateOrFail()).toBe(true);

        const bl = new CNSValidation('100000000000007').blacklist(['100000000000007']);
        expect(bl.validate()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
        expect(() => bl.validateOrFail()).toThrow('input invalid');
    });

    it('covers the dv==10 branch for 1/2 starters explicitly', () => {
        expect(new CNSValidation('100000000060018').validate()).toBe(true);
    });

    it('hits dv==11 branch (definitive CNS with dv=0)', () => {
        expect(new CNSValidation('100000000080000').validate()).toBe(true);
    });

    it('validates CNS starting with 2 (definitive)', () => {
        expect(new CNSValidation('200000000000003').validate()).toBe(true);
    });

    it('validates CNS starting with 8 (provisional)', () => {
        expect(new CNSValidation('800000000000001').validate()).toBe(true);
    });

    it('validates CNS starting with 9 (provisional)', () => {
        expect(new CNSValidation('900000000000008').validate()).toBe(true);
    });

    it('rejects CNS starting with 4, 5, or 6', () => {
        expect(new CNSValidation('400000000000000').validate()).toBe(false);
        expect(new CNSValidation('500000000000000').validate()).toBe(false);
        expect(new CNSValidation('600000000000000').validate()).toBe(false);
    });

    it('rejects CNS starting with 3 even when sum%11==0 under provisional logic', () => {
        expect(new CNSValidation('300000000000050').validate()).toBe(false);
    });

    it('validates provisional CNS that would fail definitive logic', () => {
        expect(new CNSValidation('700000000012100').validate()).toBe(true);
    });

    it('rejects CNS with valid digits but extra length', () => {
        expect(new CNSValidation('1000000000600180').validate()).toBe(false);
    });
});
