<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Exceptions\InvalidStateRuleException;
use SafeAccess\Identum\Identum;

/*
 * generate() is non-deterministic, so it is verified by a property:
 * every generated value must pass the matching validator (isValid()).
 */

describe('Identum generators produce valid documents', function () {

    $simple = [
        'CPF' => ['generateCpf', 'cpf'],
        'CNPJ' => ['generateCnpj', 'cnpj'],
        'CNH' => ['generateCnh', 'cnh'],
        'CEP' => ['generateCep', 'cep'],
        'CNS' => ['generateCns', 'cns'],
        'PIS' => ['generatePis', 'pis'],
        'RENAVAM' => ['generateRenavam', 'renavam'],
        'Plate' => ['generatePlaca', 'placa'],
        'Voter Title' => ['generateTituloEleitor', 'tituloEleitor'],
    ];

    foreach ($simple as $label => [$gen, $val]) {
        // 300 samples, not 50: several generators have a `remainder === 10 -> 0`
        // arm that only executes for ~1 in 11 draws. At 50 samples that line went
        // uncovered on roughly 1 run in 120, failing the --min=100 coverage gate
        // for reasons unrelated to the change under test.
        it("generates valid {$label} values (300 samples)", function () use ($gen, $val) {
            for ($i = 0; $i < 300; $i++) {
                $value = Identum::$gen();
                expect(Identum::$val($value)->isValid())->toBeTrue();
            }
        });
    }

    it('generates a valid IE for every state (5 samples each)', function () {
        foreach (StateEnum::cases() as $state) {
            for ($i = 0; $i < 5; $i++) {
                $value = Identum::generateIe($state);
                expect(Identum::ie($value, $state)->isValid())->toBeTrue();
            }
        }
    });

    it('accepts a raw IBGE code in generateIe()', function () {
        $value = Identum::generateIe(StateEnum::SP->value);
        expect(Identum::ie($value, StateEnum::SP)->isValid())->toBeTrue();
    });

    it('throws for an unknown state in generateIe()', function () {
        expect(fn () => Identum::generateIe(0))->toThrow(InvalidStateRuleException::class);
    });

    it('honors the formatted flag where a mask exists', function () {
        expect(Identum::generateCpf(true))->toMatch('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/');
        expect(Identum::generateCnpj(true))->toMatch('#^\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}$#');
        expect(Identum::generateCep(true))->toMatch('/^\d{5}-\d{3}$/');
        expect(Identum::generatePis(true))->toMatch('/^\d{3}\.\d{5}\.\d{2}-\d$/');
        expect(Identum::generateCns(true))->toMatch('/^\d{3} \d{4} \d{4} \d{4}$/');
    });

    it('generates unmasked by default', function () {
        expect(Identum::generateCpf())->toMatch('/^\d{11}$/');
        expect(Identum::generateCnpj())->toMatch('/^\d{14}$/');
    });
});
