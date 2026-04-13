import { describe, it, expect } from 'vitest';
import { Identum, StateEnum } from '../src/index.js';

/**
 * Cross-language parity tests.
 *
 * These cases are the canonical ground truth that the PHP package must also
 * return for the same input.  The matching PHP test file lives at
 * packages/php/tests/Unit/ParityTest.php and must stay in sync with this file.
 */
describe('Parity — same input, same output in PHP and TypeScript', () => {

    describe('CPF', () => {
        it('accepts a valid CPF (formatted)', () => expect(Identum.cpf('529.982.247-25').validate()).toBe(true));
        it('accepts a valid CPF (digits only)', () => expect(Identum.cpf('52998224725').validate()).toBe(true));
        it('rejects all-same-digit CPF', () => expect(Identum.cpf('111.111.111-11').validate()).toBe(false));
        it('rejects invalid CPF', () => expect(Identum.cpf('000.000.000-00').validate()).toBe(false));
    });

    describe('CNPJ', () => {
        it('accepts a valid numeric CNPJ (formatted)', () => expect(Identum.cnpj('84.773.274/0001-03').validate()).toBe(true));
        it('accepts a valid numeric CNPJ (digits only)', () => expect(Identum.cnpj('84773274000103').validate()).toBe(true));
        it('accepts a valid alphanumeric CNPJ', () => expect(Identum.cnpj('A0000000000032').validate()).toBe(true));
        it('rejects all-same-digit CNPJ', () => expect(Identum.cnpj('11.111.111/1111-11').validate()).toBe(false));
    });

    describe('CNH', () => {
        it('accepts a valid CNH', () => expect(Identum.cnh('22522791508').validate()).toBe(true));
        it('rejects an invalid CNH', () => expect(Identum.cnh('00000000000').validate()).toBe(false));
    });

    describe('CEP', () => {
        it('accepts a valid CEP (formatted)', () => expect(Identum.cep('78000-000').validate()).toBe(true));
        it('accepts a valid CEP (digits only)', () => expect(Identum.cep('78000000').validate()).toBe(true));
        it('rejects a too-short CEP', () => expect(Identum.cep('1234567').validate()).toBe(false));
    });

    describe('CNS', () => {
        it('accepts a valid CNS (starts with 1)', () => expect(Identum.cns('100000000060018').validate()).toBe(true));
        it('accepts a valid CNS (starts with 7)', () => expect(Identum.cns('700000000000005').validate()).toBe(true));
        it('rejects an invalid CNS', () => expect(Identum.cns('000000000000000').validate()).toBe(false));
    });

    describe('PIS', () => {
        it('accepts a valid PIS (formatted)', () => expect(Identum.pis('329.9506.158-9').validate()).toBe(true));
        it('accepts a valid PIS (digits only)', () => expect(Identum.pis('32995061589').validate()).toBe(true));
        it('rejects an invalid PIS', () => expect(Identum.pis('00000000000').validate()).toBe(false));
    });

    describe('IE', () => {
        it('accepts a valid IE — SP', () => expect(Identum.ie('343173196450', StateEnum.SP).validate()).toBe(true));
        it('accepts a valid IE — BA', () => expect(Identum.ie('153189458', StateEnum.BA).validate()).toBe(true));
        it('accepts a valid IE — MG', () => expect(Identum.ie('7908930932562', StateEnum.MG).validate()).toBe(true));
        it('rejects an invalid IE — SP', () => expect(Identum.ie('000000000000', StateEnum.SP).validate()).toBe(false));
    });

    describe('RENAVAM', () => {
        it('accepts a valid RENAVAM', () => expect(Identum.renavam('60390908553').validate()).toBe(true));
        it('rejects an invalid RENAVAM', () => expect(Identum.renavam('00000000000').validate()).toBe(false));
    });

    describe('Plate (Mercosul)', () => {
        it('accepts a valid Mercosul plate', () => expect(Identum.placa('ABC1D23').validate()).toBe(true));
        it('rejects an old-format plate (LLLDDD)', () => expect(Identum.placa('ABC1234').validate()).toBe(false));
    });

    describe('Voter Title', () => {
        it('accepts a valid Voter Title', () => expect(Identum.tituloEleitor('123456781295').validate()).toBe(true));
        it('rejects an invalid Voter Title', () => expect(Identum.tituloEleitor('000000000000').validate()).toBe(false));
    });

});
