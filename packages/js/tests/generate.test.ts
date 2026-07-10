import { describe, it, expect } from 'vitest';
import { Identum } from '../src/index.js';
import { StateEnum } from '../src/assets/ie/state-enum.js';
import { InvalidStateRuleException } from '../src/exceptions/invalid-state-rule-exception.js';

/*
 * generate() is non-deterministic, so it is verified by a property:
 * every generated value must pass the matching validator (isValid()).
 */

describe('Identum generators produce valid documents', () => {
    const simple: Array<[string, () => string, (v: string) => boolean]> = [
        ['CPF', () => Identum.generateCpf(), (v) => Identum.cpf(v).isValid()],
        ['CNPJ', () => Identum.generateCnpj(), (v) => Identum.cnpj(v).isValid()],
        ['CNH', () => Identum.generateCnh(), (v) => Identum.cnh(v).isValid()],
        ['CEP', () => Identum.generateCep(), (v) => Identum.cep(v).isValid()],
        ['CNS', () => Identum.generateCns(), (v) => Identum.cns(v).isValid()],
        ['PIS', () => Identum.generatePis(), (v) => Identum.pis(v).isValid()],
        ['RENAVAM', () => Identum.generateRenavam(), (v) => Identum.renavam(v).isValid()],
        ['Plate', () => Identum.generatePlaca(), (v) => Identum.placa(v).isValid()],
        ['Voter Title', () => Identum.generateTituloEleitor(), (v) => Identum.tituloEleitor(v).isValid()],
    ];

    for (const [label, gen, val] of simple) {
        it(`generates valid ${label} values (50 samples)`, () => {
            for (let i = 0; i < 50; i++) {
                expect(val(gen())).toBe(true);
            }
        });
    }

    it('generates a valid IE for every state (5 samples each)', () => {
        for (const code of Object.values(StateEnum)) {
            for (let i = 0; i < 5; i++) {
                const value = Identum.generateIe(code);
                expect(Identum.ie(value, code).isValid()).toBe(true);
            }
        }
    });

    it('throws for an unknown state in generateIe()', () => {
        expect(() => Identum.generateIe(0)).toThrow(InvalidStateRuleException);
    });

    it('honors the formatted flag where a mask exists', () => {
        expect(Identum.generateCpf(true)).toMatch(/^\d{3}\.\d{3}\.\d{3}-\d{2}$/);
        expect(Identum.generateCnpj(true)).toMatch(/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/);
        expect(Identum.generateCep(true)).toMatch(/^\d{5}-\d{3}$/);
        expect(Identum.generatePis(true)).toMatch(/^\d{3}\.\d{5}\.\d{2}-\d$/);
        expect(Identum.generateCns(true)).toMatch(/^\d{3} \d{4} \d{4} \d{4}$/);
    });

    it('generates unmasked by default', () => {
        expect(Identum.generateCpf()).toMatch(/^\d{11}$/);
        expect(Identum.generateCnpj()).toMatch(/^\d{14}$/);
    });
});
