<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\IE\IEValidation;
use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Exceptions\InvalidStateRuleException;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(IEValidation::class, function () {

    it('should be able validate IE - AC', function () {
        expect((new IEValidation('0151926231074', StateEnum::AC))->validate())->toBeTrue();
        expect((new IEValidation('0177883464106', StateEnum::AC))->validate())->toBeTrue();

        expect((new IEValidation('0151926231075', StateEnum::AC))->validate())->toBeFalse();
        expect((new IEValidation('015192623107', StateEnum::AC))->validate())->toBeFalse();
        expect((new IEValidation('0000000000000', StateEnum::AC))->validate())->toBeFalse();
        expect((new IEValidation('0251926231074', StateEnum::AC))->validate())->toBeFalse();
        expect((new IEValidation('0151926231084', StateEnum::AC))->validate())->toBeFalse();
    });

    it('should be able validate IE - AL', function () {
        expect((new IEValidation('249615797', StateEnum::AL))->validate())->toBeTrue();
        expect((new IEValidation('240000030', StateEnum::AL))->validate())->toBeTrue();

        expect((new IEValidation('249615796', StateEnum::AL))->validate())->toBeFalse();
        expect((new IEValidation('24961579', StateEnum::AL))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::AL))->validate())->toBeFalse();
        expect((new IEValidation('239615797', StateEnum::AL))->validate())->toBeFalse();
    });

    it('should be able validate IE - AM', function () {
        expect((new IEValidation('041162765', StateEnum::AM))->validate())->toBeTrue();

        expect((new IEValidation('041162764', StateEnum::AM))->validate())->toBeFalse();
        expect((new IEValidation('04116276', StateEnum::AM))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::AM))->validate())->toBeFalse();
        expect((new IEValidation('051162765', StateEnum::AM))->validate())->toBeFalse();
    });

    it('should be able validate IE - AP', function () {
        expect((new IEValidation('031348440', StateEnum::AP))->validate())->toBeTrue();
        expect((new IEValidation('030170071', StateEnum::AP))->validate())->toBeTrue();

        expect((new IEValidation('031348441', StateEnum::AP))->validate())->toBeFalse();
        expect((new IEValidation('03134844', StateEnum::AP))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::AP))->validate())->toBeFalse();

        expect((new IEValidation('030000012', StateEnum::AP))->validate())->toBeTrue();
        expect((new IEValidation('030170011', StateEnum::AP))->validate())->toBeTrue();
        expect((new IEValidation('030200008', StateEnum::AP))->validate())->toBeTrue();

        expect((new IEValidation('041348440', StateEnum::AP))->validate())->toBeFalse();
        expect((new IEValidation('030170010', StateEnum::AP))->validate())->toBeFalse();
        expect((new IEValidation('030170020', StateEnum::AP))->validate())->toBeTrue();
    });

    it('should be able validate IE - BA', function () {
        expect((new IEValidation('153189458', StateEnum::BA))->validate())->toBeTrue();

        expect((new IEValidation('153189459', StateEnum::BA))->validate())->toBeFalse();
        expect((new IEValidation('15318945', StateEnum::BA))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::BA))->validate())->toBeFalse();

        expect((new IEValidation('827282694', StateEnum::BA))->validate())->toBeTrue();
        expect((new IEValidation('873570662', StateEnum::BA))->validate())->toBeTrue();
    });

    it('should be able validate IE - CE', function () {
        expect((new IEValidation('224901168', StateEnum::CE))->validate())->toBeTrue();

        expect((new IEValidation('224901169', StateEnum::CE))->validate())->toBeFalse();
        expect((new IEValidation('22490116', StateEnum::CE))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::CE))->validate())->toBeFalse();
    });

    it('should be able validate IE - DF', function () {
        expect((new IEValidation('0758107589725', StateEnum::DF))->validate())->toBeTrue();
        expect((new IEValidation('0740805495120', StateEnum::DF))->validate())->toBeTrue();

        expect((new IEValidation('0758107589726', StateEnum::DF))->validate())->toBeFalse();
        expect((new IEValidation('075810758972', StateEnum::DF))->validate())->toBeFalse();
        expect((new IEValidation('0000000000000', StateEnum::DF))->validate())->toBeFalse();

        expect((new IEValidation('0658107589725', StateEnum::DF))->validate())->toBeFalse();
        expect((new IEValidation('0758107589735', StateEnum::DF))->validate())->toBeFalse();
    });

    it('should be able validate IE - ES', function () {
        expect((new IEValidation('898021650', StateEnum::ES))->validate())->toBeTrue();

        expect((new IEValidation('898021651', StateEnum::ES))->validate())->toBeFalse();
        expect((new IEValidation('89802165', StateEnum::ES))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::ES))->validate())->toBeFalse();
    });

    it('should be able validate IE - GO', function () {
        expect((new IEValidation('209644419', StateEnum::GO))->validate())->toBeTrue();
        expect((new IEValidation('200000039', StateEnum::GO))->validate())->toBeTrue();

        expect((new IEValidation('209644418', StateEnum::GO))->validate())->toBeFalse();
        expect((new IEValidation('20964441', StateEnum::GO))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::GO))->validate())->toBeFalse();

        expect((new IEValidation('101031051', StateEnum::GO))->validate())->toBeTrue();
        expect((new IEValidation('200000080', StateEnum::GO))->validate())->toBeTrue();
        expect((new IEValidation('200000020', StateEnum::GO))->validate())->toBeTrue();
    });

    it('should be able validate IE - MA', function () {
        expect((new IEValidation('122107985', StateEnum::MA))->validate())->toBeTrue();

        expect((new IEValidation('122107984', StateEnum::MA))->validate())->toBeFalse();
        expect((new IEValidation('12210798', StateEnum::MA))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::MA))->validate())->toBeFalse();

        expect((new IEValidation('132107985', StateEnum::MA))->validate())->toBeFalse();
    });

    it('should be able validate IE - MG', function () {
        expect((new IEValidation('7908930932562', StateEnum::MG))->validate())->toBeTrue();
        expect((new IEValidation('2034280802330', StateEnum::MG))->validate())->toBeTrue();

        expect((new IEValidation('7908930932563', StateEnum::MG))->validate())->toBeFalse();
        expect((new IEValidation('790893093256', StateEnum::MG))->validate())->toBeFalse();
        expect((new IEValidation('0000000000000', StateEnum::MG))->validate())->toBeFalse();
        expect((new IEValidation('7908930932572', StateEnum::MG))->validate())->toBeFalse();
    });

    it('should be able validate IE - MS', function () {
        expect((new IEValidation('285164562', StateEnum::MS))->validate())->toBeTrue();

        expect((new IEValidation('285164563', StateEnum::MS))->validate())->toBeFalse();
        expect((new IEValidation('28516456', StateEnum::MS))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::MS))->validate())->toBeFalse();

        expect((new IEValidation('295164562', StateEnum::MS))->validate())->toBeFalse();
    });

    it('should be able validate IE - MT', function () {
        expect((new IEValidation('49602225160', StateEnum::MT))->validate())->toBeTrue();

        expect((new IEValidation('49602225161', StateEnum::MT))->validate())->toBeFalse();
        expect((new IEValidation('4960222516', StateEnum::MT))->validate())->toBeFalse();
        expect((new IEValidation('00000000000', StateEnum::MT))->validate())->toBeFalse();
    });

    it('should be able validate IE - PA', function () {
        expect((new IEValidation('153804912', StateEnum::PA))->validate())->toBeTrue();

        expect((new IEValidation('153804913', StateEnum::PA))->validate())->toBeFalse();
        expect((new IEValidation('15380491', StateEnum::PA))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::PA))->validate())->toBeFalse();

        expect((new IEValidation('163804912', StateEnum::PA))->validate())->toBeFalse();
    });

    it('should be able validate IE - PB', function () {
        expect((new IEValidation('870337858', StateEnum::PB))->validate())->toBeTrue();

        expect((new IEValidation('870337859', StateEnum::PB))->validate())->toBeFalse();
        expect((new IEValidation('87033785', StateEnum::PB))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::PB))->validate())->toBeFalse();
    });

    it('should be able validate IE - PI', function () {
        expect((new IEValidation('362398496', StateEnum::PI))->validate())->toBeTrue();

        expect((new IEValidation('362398497', StateEnum::PI))->validate())->toBeFalse();
        expect((new IEValidation('36239849', StateEnum::PI))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::PI))->validate())->toBeFalse();
    });

    it('should be able validate IE - PR', function () {
        expect((new IEValidation('5614714010', StateEnum::PR))->validate())->toBeTrue();

        expect((new IEValidation('5614714011', StateEnum::PR))->validate())->toBeFalse();
        expect((new IEValidation('561471401', StateEnum::PR))->validate())->toBeFalse();
        expect((new IEValidation('0000000000', StateEnum::PR))->validate())->toBeFalse();
        expect((new IEValidation('5614714910', StateEnum::PR))->validate())->toBeFalse();
    });

    it('should be able validate IE - RJ', function () {
        expect((new IEValidation('87144836', StateEnum::RJ))->validate())->toBeTrue();

        expect((new IEValidation('87144837', StateEnum::RJ))->validate())->toBeFalse();
        expect((new IEValidation('8714483', StateEnum::RJ))->validate())->toBeFalse();
        expect((new IEValidation('00000000', StateEnum::RJ))->validate())->toBeFalse();
    });

    it('should be able validate IE - RN (9 e 10 dígitos, prefixo 20)', function () {
        expect((new IEValidation('208876057', StateEnum::RN))->validate())->toBeTrue();
        expect((new IEValidation('2049262019', StateEnum::RN))->validate())->toBeTrue();
        expect((new IEValidation('2047114640', StateEnum::RN))->validate())->toBeTrue();

        expect((new IEValidation('212194671', StateEnum::RN))->validate())->toBeFalse();
        expect((new IEValidation('208876058', StateEnum::RN))->validate())->toBeFalse();
        expect((new IEValidation('2049262018', StateEnum::RN))->validate())->toBeFalse();
        expect((new IEValidation('200000000', StateEnum::RN))->validate())->toBeFalse();
        expect((new IEValidation('2000000000', StateEnum::RN))->validate())->toBeFalse();
        expect((new IEValidation('', StateEnum::RN))->validate())->toBeFalse();
    });

    it('should be able validate IE - RO (14 atual e 9 legado)', function () {
        expect((new IEValidation('27304477352498', StateEnum::RO))->validate())->toBeTrue();
        expect((new IEValidation('153354635', StateEnum::RO))->validate())->toBeTrue();
        expect((new IEValidation('000000001', StateEnum::RO))->validate())->toBeTrue();

        expect((new IEValidation('27304477352499', StateEnum::RO))->validate())->toBeFalse();
        expect((new IEValidation('153354636', StateEnum::RO))->validate())->toBeFalse();
        expect((new IEValidation('15335463', StateEnum::RO))->validate())->toBeFalse();
        expect((new IEValidation('00000000000000', StateEnum::RO))->validate())->toBeFalse();
    });

    it('should be able validate IE - RR', function () {
        expect((new IEValidation('040837043', StateEnum::RR))->validate())->toBeTrue();

        expect((new IEValidation('040837044', StateEnum::RR))->validate())->toBeFalse();
        expect((new IEValidation('04083704', StateEnum::RR))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::RR))->validate())->toBeFalse();
    });

    it('should be able validate IE - RS', function () {
        expect((new IEValidation('2556206567', StateEnum::RS))->validate())->toBeTrue();

        expect((new IEValidation('2556206568', StateEnum::RS))->validate())->toBeFalse();
        expect((new IEValidation('255620656', StateEnum::RS))->validate())->toBeFalse();
        expect((new IEValidation('0000000000', StateEnum::RS))->validate())->toBeFalse();
    });

    it('should be able validate IE - SC', function () {
        expect((new IEValidation('144260913', StateEnum::SC))->validate())->toBeTrue();

        expect((new IEValidation('144260914', StateEnum::SC))->validate())->toBeFalse();
        expect((new IEValidation('14426091', StateEnum::SC))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::SC))->validate())->toBeFalse();
    });

    it('should be able validate IE - SE', function () {
        expect((new IEValidation('496123289', StateEnum::SE))->validate())->toBeTrue();

        expect((new IEValidation('496123280', StateEnum::SE))->validate())->toBeFalse();
        expect((new IEValidation('49612328', StateEnum::SE))->validate())->toBeFalse();
        expect((new IEValidation('000000000', StateEnum::SE))->validate())->toBeFalse();
    });

    it('should be able validate IE - SP (industrial e produtor rural)', function () {
        expect((new IEValidation('000000010005', StateEnum::SP))->validate())->toBeTrue();
        expect((new IEValidation('343173197450', StateEnum::SP))->validate())->toBeFalse();

        expect((new IEValidation('343173196450', StateEnum::SP))->validate())->toBeTrue();
        expect((new IEValidation('343173196451', StateEnum::SP))->validate())->toBeFalse();

        expect((new IEValidation('P199163724045', StateEnum::SP))->validate())->toBeTrue();
        expect((new IEValidation('P199163725045', StateEnum::SP))->validate())->toBeFalse();

        expect((new IEValidation('P199163724046', StateEnum::SP))->validate())->toBeTrue();
        expect((new IEValidation('P000000010000', StateEnum::SP))->validate())->toBeTrue();

        expect((new IEValidation('34317319645', StateEnum::SP))->validate())->toBeFalse();
        expect((new IEValidation('P19916372404', StateEnum::SP))->validate())->toBeFalse();

        expect((new IEValidation('000000000000', StateEnum::SP))->validate())->toBeFalse();
        expect((new IEValidation('P000000000000', StateEnum::SP))->validate())->toBeFalse();
    });

    it('should be able validate IE - TO (9 e 11 dígitos, pos 3–4 especiais)', function () {
        expect((new IEValidation('620150955', StateEnum::TO))->validate())->toBeTrue();
        expect((new IEValidation('73033149820', StateEnum::TO))->validate())->toBeTrue();

        expect((new IEValidation('73033149821', StateEnum::TO))->validate())->toBeFalse();
        expect((new IEValidation('73083149820', StateEnum::TO))->validate())->toBeFalse();
        expect((new IEValidation('62015095', StateEnum::TO))->validate())->toBeFalse();
    });

    it('should be able validate IE - PE (14 atual e 9 legado com 2 DVs)', function () {
        expect((new IEValidation('16622857667318', StateEnum::PE))->validate())->toBeTrue();
        expect((new IEValidation('055976506', StateEnum::PE))->validate())->toBeTrue();
        expect((new IEValidation('000000604', StateEnum::PE))->validate())->toBeTrue();

        expect((new IEValidation('16622857667319', StateEnum::PE))->validate())->toBeFalse();
        expect((new IEValidation('055976507', StateEnum::PE))->validate())->toBeFalse();
        expect((new IEValidation('05597650', StateEnum::PE))->validate())->toBeFalse();
        expect((new IEValidation('11111111111111', StateEnum::PE))->validate())->toBeFalse();
        expect((new IEValidation('16622857667310', StateEnum::PE))->validate())->toBeFalse();
        expect((new IEValidation('32753541965640', StateEnum::PE))->validate())->toBeTrue();
        expect((new IEValidation('055976516', StateEnum::PE))->validate())->toBeFalse();
    });

    it('should throw for unknown state (no mapped rule)', function () {
        expect(fn () => new IEValidation('123456789', 0))
            ->toThrow(InvalidStateRuleException::class, 'invalid state rule');
    });

    it('whitelist() overrides invalid result', function () {
        expect((new IEValidation('209644418', StateEnum::GO))->whitelist(['209644418'])->validate())->toBeTrue();
    });

    it('blacklist() overrides valid result', function () {
        expect((new IEValidation('209644419', StateEnum::GO))->blacklist(['209644419'])->validate())->toBeFalse();
    });

    it('validateOrFail() returns true when valid', function () {
        expect((new IEValidation('209644419', StateEnum::GO))->validateOrFail())->toBeTrue();
    });

    it('validateOrFail() throws ValidationException when invalid', function () {
        expect(fn () => (new IEValidation('209644418', StateEnum::GO))->validateOrFail())
            ->toThrow(ValidationException::class, 'input invalid');
    });

    it('validateOrFail() respects whitelist and blacklist', function () {
        expect((new IEValidation('209644418', StateEnum::GO))->whitelist(['209644418'])->validateOrFail())->toBeTrue();
        expect(fn () => (new IEValidation('209644419', StateEnum::GO))->blacklist(['209644419'])->validateOrFail())
            ->toThrow(ValidationException::class);
    });
});
