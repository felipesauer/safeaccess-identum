<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Identum;

describe(Identum::class, function () {

    it('resolves CPF/CNPJ/CNH/PIS/CNS/RENAVAM/CEP via static aliases', function () {
        // CPF
        $cpf = Identum::cpf('864.600.120-24');
        expect($cpf)->toBeInstanceOf(\SafeAccess\Identum\Assets\CPF\CPFValidation::class)
            ->and($cpf->validate())->toBeTrue();

        // CNPJ (numérico)
        $cnpj = Identum::cnpj('84.773.274/0001-03');
        expect($cnpj)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNPJ\CNPJValidation::class)
            ->and($cnpj->validate())->toBeTrue();

        // CNPJ (alfanumérico)
        $cnpjAlpha = Identum::cnpj('A0000000000032');
        expect($cnpjAlpha)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNPJ\CNPJValidation::class)
            ->and($cnpjAlpha->validate())->toBeTrue();

        // CNH
        $cnh = Identum::cnh('22522791508');
        expect($cnh)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNH\CNHValidation::class)
            ->and($cnh->validate())->toBeTrue();

        // PIS
        $pis = Identum::pis('32995061589');
        expect($pis)->toBeInstanceOf(\SafeAccess\Identum\Assets\PIS\PISValidation::class)
            ->and($pis->validate())->toBeTrue();

        // CNS
        $cns = Identum::cns('100000000000007');
        expect($cns)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNS\CNSValidation::class)
            ->and($cns->validate())->toBeTrue();

        // RENAVAM
        $renavam = Identum::renavam('60390908553');
        expect($renavam)->toBeInstanceOf(\SafeAccess\Identum\Assets\RENAVAM\RenavamValidation::class)
            ->and($renavam->validate())->toBeTrue();

        // CEP
        $cep = Identum::cep('01001-000');
        expect($cep)->toBeInstanceOf(\SafeAccess\Identum\Assets\CEP\CEPValidation::class)
            ->and($cep->validate())->toBeTrue();
    });

    it('passes through extra constructor parameters (ex: IE needs state)', function () {
        $ie = Identum::ie('209644419', StateEnum::GO);
        expect($ie)->toBeInstanceOf(\SafeAccess\Identum\Assets\IE\IEValidation::class)
            ->and($ie->validate())->toBeTrue();
    });

    it('exposes alias map and specific entries via getAlias()', function () {
        $map = Identum::getAlias();
        expect($map)->toBeArray()
            ->and(array_keys($map))->toContain(
                'cpf',
                'cnpj',
                'cnh',
                'pis',
                'cns',
                'ie',
                'renavam',
                'cep'
            );

        $cnpj = Identum::getAlias('cnpj');
        expect($cnpj)->toBe(\SafeAccess\Identum\Assets\CNPJ\CNPJValidation::class);

        $unknown = Identum::getAlias('foo');
        expect($unknown)->toBeFalse();
    });

    it('throws when alias does not exist', function () {
        expect(fn () => Identum::foo('anything'))->toThrow(RuntimeException::class);
    });

    it('returned validator supports whitelist()', function () {
        expect(Identum::cpf('323.543.123-43')->whitelist(['323.543.123-43'])->validate())->toBeTrue();
    });

    it('returned validator supports blacklist()', function () {
        expect(Identum::cpf('864.600.120-24')->blacklist(['864.600.120-24'])->validate())->toBeFalse();
    });

    it('returned validator supports validateOrFail()', function () {
        expect(Identum::cpf('864.600.120-24')->validateOrFail())->toBeTrue();
        expect(fn () => Identum::cpf('323.543.123-43')->validateOrFail())
            ->toThrow(\SafeAccess\Identum\Exceptions\ValidationException::class, 'input invalid');
    });
});
