<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Identum;

/**
 * Cross-language parity tests.
 *
 * These cases are the canonical ground truth that the TypeScript package
 * must also return for the same input.  The matching JS test file lives at
 * packages/js/tests/parity.test.ts and must stay in sync with this file.
 */
describe('Parity — same input, same output in PHP and TypeScript', function () {

    describe('CPF', function () {
        it('accepts a valid CPF (formatted)', fn () => expect(Identum::cpf('529.982.247-25')->validate())->toBeTrue());
        it('accepts a valid CPF (digits only)', fn () => expect(Identum::cpf('52998224725')->validate())->toBeTrue());
        it('rejects all-same-digit CPF', fn () => expect(Identum::cpf('111.111.111-11')->validate())->toBeFalse());
        it('rejects invalid CPF', fn () => expect(Identum::cpf('000.000.000-00')->validate())->toBeFalse());
    });

    describe('CNPJ', function () {
        it('accepts a valid numeric CNPJ (formatted)', fn () => expect(Identum::cnpj('84.773.274/0001-03')->validate())->toBeTrue());
        it('accepts a valid numeric CNPJ (digits only)', fn () => expect(Identum::cnpj('84773274000103')->validate())->toBeTrue());
        it('accepts a valid alphanumeric CNPJ', fn () => expect(Identum::cnpj('A0000000000032')->validate())->toBeTrue());
        it('rejects all-same-digit CNPJ', fn () => expect(Identum::cnpj('11.111.111/1111-11')->validate())->toBeFalse());
    });

    describe('CNH', function () {
        it('accepts a valid CNH', fn () => expect(Identum::cnh('22522791508')->validate())->toBeTrue());
        it('rejects an invalid CNH', fn () => expect(Identum::cnh('00000000000')->validate())->toBeFalse());
    });

    describe('CEP', function () {
        it('accepts a valid CEP (formatted)', fn () => expect(Identum::cep('78000-000')->validate())->toBeTrue());
        it('accepts a valid CEP (digits only)', fn () => expect(Identum::cep('78000000')->validate())->toBeTrue());
        it('rejects a too-short CEP', fn () => expect(Identum::cep('1234567')->validate())->toBeFalse());
    });

    describe('CNS', function () {
        it('accepts a valid CNS (starts with 1)', fn () => expect(Identum::cns('100000000060018')->validate())->toBeTrue());
        it('accepts a valid CNS (starts with 7)', fn () => expect(Identum::cns('700000000000005')->validate())->toBeTrue());
        it('rejects an invalid CNS', fn () => expect(Identum::cns('000000000000000')->validate())->toBeFalse());
    });

    describe('PIS', function () {
        it('accepts a valid PIS (formatted)', fn () => expect(Identum::pis('329.9506.158-9')->validate())->toBeTrue());
        it('accepts a valid PIS (digits only)', fn () => expect(Identum::pis('32995061589')->validate())->toBeTrue());
        it('rejects an invalid PIS', fn () => expect(Identum::pis('00000000000')->validate())->toBeFalse());
    });

    describe('IE', function () {
        it('accepts a valid IE — SP', fn () => expect(Identum::ie('343.173.196.450', StateEnum::SP)->validate())->toBeTrue());
        it('accepts a valid IE — BA', fn () => expect(Identum::ie('153189458', StateEnum::BA)->validate())->toBeTrue());
        it('accepts a valid IE — MG', fn () => expect(Identum::ie('7908930932562', StateEnum::MG)->validate())->toBeTrue());
        it('rejects an invalid IE — SP', fn () => expect(Identum::ie('000.000.000.000', StateEnum::SP)->validate())->toBeFalse());
    });

    describe('RENAVAM', function () {
        it('accepts a valid RENAVAM', fn () => expect(Identum::renavam('60390908553')->validate())->toBeTrue());
        it('rejects an invalid RENAVAM', fn () => expect(Identum::renavam('00000000000')->validate())->toBeFalse());
    });

    describe('Plate (Mercosul)', function () {
        it('accepts a valid Mercosul plate', fn () => expect(Identum::placa('ABC1D23')->validate())->toBeTrue());
        it('rejects an old-format plate (LLLDDD)', fn () => expect(Identum::placa('ABC1234')->validate())->toBeFalse());
    });

    describe('Voter Title', function () {
        it('accepts a valid Voter Title', fn () => expect(Identum::tituloEleitor('123456781295')->validate())->toBeTrue());
        it('rejects an invalid Voter Title', fn () => expect(Identum::tituloEleitor('000000000000')->validate())->toBeFalse());
    });

});
