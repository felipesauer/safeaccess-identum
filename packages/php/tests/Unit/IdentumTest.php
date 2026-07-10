<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Identum;

describe(Identum::class, function () {

    it('resolves CPF/CNPJ/CNH/PIS/CNS/RENAVAM/CEP via static factories', function () {
        // CPF
        $cpf = Identum::cpf('864.600.120-24');
        expect($cpf)->toBeInstanceOf(\SafeAccess\Identum\Assets\CPF\CPFValidation::class)
            ->and($cpf->isValid())->toBeTrue();

        // CNPJ (numérico)
        $cnpj = Identum::cnpj('84.773.274/0001-03');
        expect($cnpj)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNPJ\CNPJValidation::class)
            ->and($cnpj->isValid())->toBeTrue();

        // CNPJ (alfanumérico)
        $cnpjAlpha = Identum::cnpj('A0000000000032');
        expect($cnpjAlpha)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNPJ\CNPJValidation::class)
            ->and($cnpjAlpha->isValid())->toBeTrue();

        // CNH
        $cnh = Identum::cnh('22522791508');
        expect($cnh)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNH\CNHValidation::class)
            ->and($cnh->isValid())->toBeTrue();

        // PIS
        $pis = Identum::pis('32995061589');
        expect($pis)->toBeInstanceOf(\SafeAccess\Identum\Assets\PIS\PISValidation::class)
            ->and($pis->isValid())->toBeTrue();

        // CNS
        $cns = Identum::cns('100000000000007');
        expect($cns)->toBeInstanceOf(\SafeAccess\Identum\Assets\CNS\CNSValidation::class)
            ->and($cns->isValid())->toBeTrue();

        // RENAVAM
        $renavam = Identum::renavam('60390908553');
        expect($renavam)->toBeInstanceOf(\SafeAccess\Identum\Assets\RENAVAM\RenavamValidation::class)
            ->and($renavam->isValid())->toBeTrue();

        // CEP
        $cep = Identum::cep('01001-000');
        expect($cep)->toBeInstanceOf(\SafeAccess\Identum\Assets\CEP\CEPValidation::class)
            ->and($cep->isValid())->toBeTrue();
    });

    it('resolves Plate and Voter Title via their Portuguese aliases', function () {
        expect(Identum::placa('ABC1D23'))
            ->toBeInstanceOf(\SafeAccess\Identum\Assets\Plate\PlateMercosulValidation::class);
        expect(Identum::tituloEleitor('123456781295'))
            ->toBeInstanceOf(\SafeAccess\Identum\Assets\Voter\VoterTitleValidation::class);
    });

    it('passes through extra constructor parameters (ex: IE needs state)', function () {
        $ie = Identum::ie('209644419', StateEnum::GO);
        expect($ie)->toBeInstanceOf(\SafeAccess\Identum\Assets\IE\IEValidation::class)
            ->and($ie->isValid())->toBeTrue();
    });

    it('resolves the 2.0 catalog: certidao, cartao and pix', function () {
        $certidao = Identum::certidao('00188301551987100018050000056665');
        expect($certidao)->toBeInstanceOf(\SafeAccess\Identum\Assets\Certidao\CertidaoValidation::class)
            ->and($certidao->isValid())->toBeTrue();

        $cartao = Identum::cartao('4111111111111111');
        expect($cartao)->toBeInstanceOf(\SafeAccess\Identum\Assets\Cartao\CartaoValidation::class)
            ->and($cartao->isValid())->toBeTrue();

        $pix = Identum::pix('pix@bcb.gov.br');
        expect($pix)->toBeInstanceOf(\SafeAccess\Identum\Assets\Pix\PixValidation::class)
            ->and($pix->isValid())->toBeTrue();
    });

    it('returned validator supports allowList()', function () {
        expect(Identum::cpf('323.543.123-43')->allowList(['323.543.123-43'])->isValid())->toBeTrue();
    });

    it('returned validator supports denyList()', function () {
        expect(Identum::cpf('864.600.120-24')->denyList(['864.600.120-24'])->isValid())->toBeFalse();
    });

    it('returned validator supports validateOrFail()', function () {
        expect(Identum::cpf('864.600.120-24')->validateOrFail())->toBeNull();
        expect(fn () => Identum::cpf('323.543.123-43')->validateOrFail())
            ->toThrow(\SafeAccess\Identum\Exceptions\ValidationException::class, 'cpf: bad_check_digit');
    });
});
