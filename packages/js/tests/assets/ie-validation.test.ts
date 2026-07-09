import { describe, it, expect } from 'vitest';
import { IEValidation } from '../../src/assets/ie/ie-validation.js';
import { StateEnum } from '../../src/assets/ie/state-enum.js';
import { InvalidStateRuleException } from '../../src/exceptions/invalid-state-rule-exception.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(IEValidation.name, () => {
    describe('AC', () => {
        it('validates', () => {
            expect(new IEValidation('0151926231074', StateEnum.AC).isValid()).toBe(true);
            expect(new IEValidation('0177883464106', StateEnum.AC).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('0151926231075', StateEnum.AC).isValid()).toBe(false);
            expect(new IEValidation('015192623107', StateEnum.AC).isValid()).toBe(false);
            expect(new IEValidation('0000000000000', StateEnum.AC).isValid()).toBe(false);
            expect(new IEValidation('0251926231074', StateEnum.AC).isValid()).toBe(false);
            expect(new IEValidation('0151926231084', StateEnum.AC).isValid()).toBe(false);
        });
        it('handles rest edge cases for DV1', () => {
            expect(new IEValidation('0100000020008', StateEnum.AC).isValid()).toBe(true);
            expect(new IEValidation('0100000050004', StateEnum.AC).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case for DV1 (dv forced to 9)', () => {
            expect(new IEValidation('0100000000597', StateEnum.AC).isValid()).toBe(true);
        });
        it('rejects AC with correct dv1 but wrong dv2', () => {
            expect(new IEValidation('0151926231075', StateEnum.AC).isValid()).toBe(false);
        });
    });

    describe('AL', () => {
        it('validates', () => {
            expect(new IEValidation('249615797', StateEnum.AL).isValid()).toBe(true);
            expect(new IEValidation('240000030', StateEnum.AL).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('249615796', StateEnum.AL).isValid()).toBe(false);
            expect(new IEValidation('24961579', StateEnum.AL).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.AL).isValid()).toBe(false);
            expect(new IEValidation('239615797', StateEnum.AL).isValid()).toBe(false);
        });
        it('handles rest and dv edge cases', () => {
            expect(new IEValidation('240000080', StateEnum.AL).isValid()).toBe(true);
            expect(new IEValidation('240000056', StateEnum.AL).isValid()).toBe(true);
        });
    });

    describe('AM', () => {
        it('validates', () => {
            expect(new IEValidation('041162765', StateEnum.AM).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('041162764', StateEnum.AM).isValid()).toBe(false);
            expect(new IEValidation('04116276', StateEnum.AM).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.AM).isValid()).toBe(false);
            expect(new IEValidation('051162765', StateEnum.AM).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('040000060', StateEnum.AM).isValid()).toBe(true);
            expect(new IEValidation('040000010', StateEnum.AM).isValid()).toBe(true);
            expect(new IEValidation('040000036', StateEnum.AM).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('040000079', StateEnum.AM).isValid()).toBe(true);
        });
    });

    describe('AP', () => {
        it('validates', () => {
            expect(new IEValidation('031348440', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('030170071', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('030000012', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('030170011', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('030200008', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('030170020', StateEnum.AP).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('031348441', StateEnum.AP).isValid()).toBe(false);
            expect(new IEValidation('03134844', StateEnum.AP).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.AP).isValid()).toBe(false);
            expect(new IEValidation('041348440', StateEnum.AP).isValid()).toBe(false);
            expect(new IEValidation('030170010', StateEnum.AP).isValid()).toBe(false);
        });
        it('handles boundary and dv edge cases', () => {
            expect(new IEValidation('030170007', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('030190225', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('030000080', StateEnum.AP).isValid()).toBe(true);
            expect(new IEValidation('031000002', StateEnum.AP).isValid()).toBe(true);
        });
        it('validates AP below range1 boundary (p=0, dConst=0)', () => {
            expect(new IEValidation('030000009', StateEnum.AP).isValid()).toBe(true);
        });
    });

    describe('BA', () => {
        it('validates', () => {
            expect(new IEValidation('153189458', StateEnum.BA).isValid()).toBe(true);
            expect(new IEValidation('827282694', StateEnum.BA).isValid()).toBe(true);
            expect(new IEValidation('873570662', StateEnum.BA).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('153189459', StateEnum.BA).isValid()).toBe(false);
            expect(new IEValidation('15318945', StateEnum.BA).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.BA).isValid()).toBe(false);
        });
        it('validates 8-digit BA and diverse mod-paths', () => {
            expect(new IEValidation('10000063', StateEnum.BA).isValid()).toBe(true);
            expect(new IEValidation('160000005', StateEnum.BA).isValid()).toBe(true);
            expect(new IEValidation('190000066', StateEnum.BA).isValid()).toBe(true);
        });
    });

    describe('CE', () => {
        it('validates', () => {
            expect(new IEValidation('224901168', StateEnum.CE).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('224901169', StateEnum.CE).isValid()).toBe(false);
            expect(new IEValidation('22490116', StateEnum.CE).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.CE).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('000000140', StateEnum.CE).isValid()).toBe(true);
            expect(new IEValidation('000000060', StateEnum.CE).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('000000019', StateEnum.CE).isValid()).toBe(true);
        });
    });

    describe('DF', () => {
        it('validates', () => {
            expect(new IEValidation('0758107589725', StateEnum.DF).isValid()).toBe(true);
            expect(new IEValidation('0740805495120', StateEnum.DF).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('0758107589726', StateEnum.DF).isValid()).toBe(false);
            expect(new IEValidation('075810758972', StateEnum.DF).isValid()).toBe(false);
            expect(new IEValidation('0000000000000', StateEnum.DF).isValid()).toBe(false);
            expect(new IEValidation('0658107589725', StateEnum.DF).isValid()).toBe(false);
            expect(new IEValidation('0758107589735', StateEnum.DF).isValid()).toBe(false);
        });
        it('handles rest edge cases for DV1', () => {
            expect(new IEValidation('0700000030001', StateEnum.DF).isValid()).toBe(true);
            expect(new IEValidation('0700000060008', StateEnum.DF).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case for DV1 (dv forced to 9)', () => {
            expect(new IEValidation('0700000000790', StateEnum.DF).isValid()).toBe(true);
        });
        it('rejects DF with correct dv1 but wrong dv2', () => {
            expect(new IEValidation('0758107589720', StateEnum.DF).isValid()).toBe(false);
        });
    });

    describe('ES', () => {
        it('validates', () => {
            expect(new IEValidation('898021650', StateEnum.ES).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('898021651', StateEnum.ES).isValid()).toBe(false);
            expect(new IEValidation('89802165', StateEnum.ES).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.ES).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('000000140', StateEnum.ES).isValid()).toBe(true);
            expect(new IEValidation('000000060', StateEnum.ES).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('000000019', StateEnum.ES).isValid()).toBe(true);
        });
    });

    describe('GO', () => {
        it('validates', () => {
            expect(new IEValidation('209644419', StateEnum.GO).isValid()).toBe(true);
            expect(new IEValidation('200000039', StateEnum.GO).isValid()).toBe(true);
            expect(new IEValidation('101031051', StateEnum.GO).isValid()).toBe(true);
            expect(new IEValidation('200000080', StateEnum.GO).isValid()).toBe(true);
            expect(new IEValidation('200000020', StateEnum.GO).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('209644418', StateEnum.GO).isValid()).toBe(false);
            expect(new IEValidation('20964441', StateEnum.GO).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.GO).isValid()).toBe(false);
        });
        it('handles rest==1 outside special range (dv=0)', () => {
            expect(new IEValidation('000003000', StateEnum.GO).isValid()).toBe(true);
        });
        it('handles rest==1 at upper boundary of special range (num=10119997)', () => {
            expect(new IEValidation('101199971', StateEnum.GO).isValid()).toBe(true);
        });
    });

    describe('MA', () => {
        it('validates', () => {
            expect(new IEValidation('122107985', StateEnum.MA).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('122107984', StateEnum.MA).isValid()).toBe(false);
            expect(new IEValidation('12210798', StateEnum.MA).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.MA).isValid()).toBe(false);
            expect(new IEValidation('132107985', StateEnum.MA).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('120000040', StateEnum.MA).isValid()).toBe(true);
            expect(new IEValidation('120000130', StateEnum.MA).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('120000059', StateEnum.MA).isValid()).toBe(true);
        });
    });

    describe('MG', () => {
        it('validates', () => {
            expect(new IEValidation('7908930932562', StateEnum.MG).isValid()).toBe(true);
            expect(new IEValidation('2034280802330', StateEnum.MG).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('7908930932563', StateEnum.MG).isValid()).toBe(false);
            expect(new IEValidation('790893093256', StateEnum.MG).isValid()).toBe(false);
            expect(new IEValidation('0000000000000', StateEnum.MG).isValid()).toBe(false);
            expect(new IEValidation('7908930932572', StateEnum.MG).isValid()).toBe(false);
        });
        it('handles d1==0 edge case (sum%10==0)', () => {
            expect(new IEValidation('0000000190004', StateEnum.MG).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case for d2 (dv forced to 9)', () => {
            expect(new IEValidation('0000000001589', StateEnum.MG).isValid()).toBe(true);
        });
    });

    describe('MS', () => {
        it('validates', () => {
            expect(new IEValidation('285164562', StateEnum.MS).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('285164563', StateEnum.MS).isValid()).toBe(false);
            expect(new IEValidation('28516456', StateEnum.MS).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.MS).isValid()).toBe(false);
            expect(new IEValidation('295164562', StateEnum.MS).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('280000030', StateEnum.MS).isValid()).toBe(true);
            expect(new IEValidation('280000090', StateEnum.MS).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('280000049', StateEnum.MS).isValid()).toBe(true);
        });
    });

    describe('MT', () => {
        it('validates', () => {
            expect(new IEValidation('49602225160', StateEnum.MT).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('49602225161', StateEnum.MT).isValid()).toBe(false);
            expect(new IEValidation('4960222516', StateEnum.MT).isValid()).toBe(false);
            expect(new IEValidation('00000000000', StateEnum.MT).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('00000000140', StateEnum.MT).isValid()).toBe(true);
            expect(new IEValidation('00000000060', StateEnum.MT).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('00000000019', StateEnum.MT).isValid()).toBe(true);
        });
    });

    describe('PA', () => {
        it('validates', () => {
            expect(new IEValidation('153804912', StateEnum.PA).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('153804913', StateEnum.PA).isValid()).toBe(false);
            expect(new IEValidation('15380491', StateEnum.PA).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.PA).isValid()).toBe(false);
            expect(new IEValidation('163804912', StateEnum.PA).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('150000030', StateEnum.PA).isValid()).toBe(true);
            expect(new IEValidation('150000090', StateEnum.PA).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('150000049', StateEnum.PA).isValid()).toBe(true);
        });
    });

    describe('PB', () => {
        it('validates', () => {
            expect(new IEValidation('870337858', StateEnum.PB).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('870337859', StateEnum.PB).isValid()).toBe(false);
            expect(new IEValidation('87033785', StateEnum.PB).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.PB).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('000000140', StateEnum.PB).isValid()).toBe(true);
            expect(new IEValidation('000000060', StateEnum.PB).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('000000019', StateEnum.PB).isValid()).toBe(true);
        });
    });

    describe('PE', () => {
        it('validates', () => {
            expect(new IEValidation('16622857667318', StateEnum.PE).isValid()).toBe(true);
            expect(new IEValidation('055976506', StateEnum.PE).isValid()).toBe(true);
            expect(new IEValidation('000000604', StateEnum.PE).isValid()).toBe(true);
            expect(new IEValidation('32753541965640', StateEnum.PE).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('16622857667319', StateEnum.PE).isValid()).toBe(false);
            expect(new IEValidation('055976507', StateEnum.PE).isValid()).toBe(false);
            expect(new IEValidation('05597650', StateEnum.PE).isValid()).toBe(false);
            expect(new IEValidation('11111111111111', StateEnum.PE).isValid()).toBe(false);
            expect(new IEValidation('16622857667310', StateEnum.PE).isValid()).toBe(false);
            expect(new IEValidation('055976516', StateEnum.PE).isValid()).toBe(false);
        });
        it('handles 14-digit rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('00000000016000', StateEnum.PE).isValid()).toBe(true);
            expect(new IEValidation('00000000004000', StateEnum.PE).isValid()).toBe(true);
        });
        it('handles 14-digit rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('00000000000109', StateEnum.PE).isValid()).toBe(true);
        });
        it('validates 9-digit PE with d1 != 0 (kills weight array mutation)', () => {
            expect(new IEValidation('000000191', StateEnum.PE).isValid()).toBe(true);
        });
        it('handles 9-digit d2 >= 10 edge case (d2 forced to 0)', () => {
            expect(new IEValidation('000005070', StateEnum.PE).isValid()).toBe(true);
        });
    });

    describe('PI', () => {
        it('validates', () => {
            expect(new IEValidation('362398496', StateEnum.PI).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('362398497', StateEnum.PI).isValid()).toBe(false);
            expect(new IEValidation('36239849', StateEnum.PI).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.PI).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('000000140', StateEnum.PI).isValid()).toBe(true);
            expect(new IEValidation('000000060', StateEnum.PI).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('000000019', StateEnum.PI).isValid()).toBe(true);
        });
    });

    describe('PR', () => {
        it('validates', () => {
            expect(new IEValidation('5614714010', StateEnum.PR).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('5614714011', StateEnum.PR).isValid()).toBe(false);
            expect(new IEValidation('561471401', StateEnum.PR).isValid()).toBe(false);
            expect(new IEValidation('0000000000', StateEnum.PR).isValid()).toBe(false);
            expect(new IEValidation('5614714910', StateEnum.PR).isValid()).toBe(false);
        });
        it('handles rest edge cases for DV1 (rest==0 and rest==1)', () => {
            expect(new IEValidation('0001100009', StateEnum.PR).isValid()).toBe(true);
            expect(new IEValidation('0000900001', StateEnum.PR).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case for DV1 (dv forced to 9)', () => {
            expect(new IEValidation('0000000191', StateEnum.PR).isValid()).toBe(true);
        });
        it('rejects PR with correct dv1 but wrong dv2', () => {
            expect(new IEValidation('5614714019', StateEnum.PR).isValid()).toBe(false);
        });
    });

    describe('RJ', () => {
        it('validates', () => {
            expect(new IEValidation('87144836', StateEnum.RJ).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('87144837', StateEnum.RJ).isValid()).toBe(false);
            expect(new IEValidation('8714483', StateEnum.RJ).isValid()).toBe(false);
            expect(new IEValidation('00000000', StateEnum.RJ).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('00000140', StateEnum.RJ).isValid()).toBe(true);
            expect(new IEValidation('00000060', StateEnum.RJ).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('00000019', StateEnum.RJ).isValid()).toBe(true);
        });
    });

    describe('RN', () => {
        it('validates', () => {
            expect(new IEValidation('208876057', StateEnum.RN).isValid()).toBe(true);
            expect(new IEValidation('2049262019', StateEnum.RN).isValid()).toBe(true);
            expect(new IEValidation('2047114640', StateEnum.RN).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('212194671', StateEnum.RN).isValid()).toBe(false);
            expect(new IEValidation('208876058', StateEnum.RN).isValid()).toBe(false);
            expect(new IEValidation('2049262018', StateEnum.RN).isValid()).toBe(false);
            expect(new IEValidation('200000000', StateEnum.RN).isValid()).toBe(false);
            expect(new IEValidation('2000000000', StateEnum.RN).isValid()).toBe(false);
            expect(new IEValidation('', StateEnum.RN).isValid()).toBe(false);
        });
        it('handles rest edge cases for 9-digit (rest==0 and rest==1)', () => {
            expect(new IEValidation('200000020', StateEnum.RN).isValid()).toBe(true);
            expect(new IEValidation('200000080', StateEnum.RN).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case for 9-digit (dv forced to 9)', () => {
            expect(new IEValidation('200000039', StateEnum.RN).isValid()).toBe(true);
        });
        it('handles rest edge cases for 10-digit (rest==0 and rest==1)', () => {
            expect(new IEValidation('2000000010', StateEnum.RN).isValid()).toBe(true);
            expect(new IEValidation('2000000070', StateEnum.RN).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case for 10-digit (dv forced to 9)', () => {
            expect(new IEValidation('2000000029', StateEnum.RN).isValid()).toBe(true);
        });
    });

    describe('RO', () => {
        it('validates', () => {
            expect(new IEValidation('27304477352498', StateEnum.RO).isValid()).toBe(true);
            expect(new IEValidation('153354635', StateEnum.RO).isValid()).toBe(true);
            expect(new IEValidation('000000001', StateEnum.RO).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('27304477352499', StateEnum.RO).isValid()).toBe(false);
            expect(new IEValidation('153354636', StateEnum.RO).isValid()).toBe(false);
            expect(new IEValidation('15335463', StateEnum.RO).isValid()).toBe(false);
            expect(new IEValidation('00000000000000', StateEnum.RO).isValid()).toBe(false);
        });
        it('handles 9-digit dv>=10 wrap (dv-=10)', () => {
            expect(new IEValidation('000000400', StateEnum.RO).isValid()).toBe(true);
        });
        it('handles 14-digit rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('00000000000019', StateEnum.RO).isValid()).toBe(true);
        });
    });

    describe('RR', () => {
        it('validates', () => {
            expect(new IEValidation('040837043', StateEnum.RR).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('040837044', StateEnum.RR).isValid()).toBe(false);
            expect(new IEValidation('04083704', StateEnum.RR).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.RR).isValid()).toBe(false);
        });
        it('uses diverse values to kill weight mutations', () => {
            expect(new IEValidation('100000001', StateEnum.RR).isValid()).toBe(true);
            expect(new IEValidation('108765433', StateEnum.RR).isValid()).toBe(true);
            expect(new IEValidation('117530861', StateEnum.RR).isValid()).toBe(true);
        });
    });

    describe('RS', () => {
        it('validates', () => {
            expect(new IEValidation('2556206567', StateEnum.RS).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('2556206568', StateEnum.RS).isValid()).toBe(false);
            expect(new IEValidation('255620656', StateEnum.RS).isValid()).toBe(false);
            expect(new IEValidation('0000000000', StateEnum.RS).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('0000000160', StateEnum.RS).isValid()).toBe(true);
            expect(new IEValidation('0000000040', StateEnum.RS).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('0000000089', StateEnum.RS).isValid()).toBe(true);
        });
    });

    describe('SC', () => {
        it('validates', () => {
            expect(new IEValidation('144260913', StateEnum.SC).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('144260914', StateEnum.SC).isValid()).toBe(false);
            expect(new IEValidation('14426091', StateEnum.SC).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.SC).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('000000140', StateEnum.SC).isValid()).toBe(true);
            expect(new IEValidation('000000060', StateEnum.SC).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('000000019', StateEnum.SC).isValid()).toBe(true);
        });
    });

    describe('SE', () => {
        it('validates', () => {
            expect(new IEValidation('496123289', StateEnum.SE).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('496123280', StateEnum.SE).isValid()).toBe(false);
            expect(new IEValidation('49612328', StateEnum.SE).isValid()).toBe(false);
            expect(new IEValidation('000000000', StateEnum.SE).isValid()).toBe(false);
        });
        it('handles rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('000000140', StateEnum.SE).isValid()).toBe(true);
            expect(new IEValidation('000000060', StateEnum.SE).isValid()).toBe(true);
        });
        it('handles rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('000000019', StateEnum.SE).isValid()).toBe(true);
        });
    });

    describe('SP', () => {
        it('validates industrial', () => {
            expect(new IEValidation('000000010005', StateEnum.SP).isValid()).toBe(true);
            expect(new IEValidation('343173196450', StateEnum.SP).isValid()).toBe(true);
        });
        it('rejects industrial', () => {
            expect(new IEValidation('343173197450', StateEnum.SP).isValid()).toBe(false);
            expect(new IEValidation('343173196451', StateEnum.SP).isValid()).toBe(false);
            expect(new IEValidation('34317319645', StateEnum.SP).isValid()).toBe(false);
            expect(new IEValidation('000000000000', StateEnum.SP).isValid()).toBe(false);
        });
        it('validates rural (P prefix)', () => {
            expect(new IEValidation('P199163724045', StateEnum.SP).isValid()).toBe(true);
            expect(new IEValidation('P199163724046', StateEnum.SP).isValid()).toBe(true);
            expect(new IEValidation('P000000010000', StateEnum.SP).isValid()).toBe(true);
        });
        it('rejects rural', () => {
            expect(new IEValidation('P199163725045', StateEnum.SP).isValid()).toBe(false);
            expect(new IEValidation('P19916372404', StateEnum.SP).isValid()).toBe(false);
            expect(new IEValidation('P000000000000', StateEnum.SP).isValid()).toBe(false);
        });
        it('handles rest==10 (dv=0) edge case', () => {
            expect(new IEValidation('000003000000', StateEnum.SP).isValid()).toBe(true);
        });
    });

    describe('TO', () => {
        it('validates', () => {
            expect(new IEValidation('620150955', StateEnum.TO).isValid()).toBe(true);
            expect(new IEValidation('73033149820', StateEnum.TO).isValid()).toBe(true);
        });
        it('rejects', () => {
            expect(new IEValidation('73033149821', StateEnum.TO).isValid()).toBe(false);
            expect(new IEValidation('73083149820', StateEnum.TO).isValid()).toBe(false);
            expect(new IEValidation('62015095', StateEnum.TO).isValid()).toBe(false);
        });
        it('handles all valid mid-digit combos', () => {
            expect(new IEValidation('10010000002', StateEnum.TO).isValid()).toBe(true);
            expect(new IEValidation('10020000002', StateEnum.TO).isValid()).toBe(true);
            expect(new IEValidation('10990000002', StateEnum.TO).isValid()).toBe(true);
        });
        it('handles 9-digit rest edge cases (rest==0 and rest==1)', () => {
            expect(new IEValidation('000017000', StateEnum.TO).isValid()).toBe(true);
            expect(new IEValidation('000003000', StateEnum.TO).isValid()).toBe(true);
        });
        it('handles 9-digit rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('000000019', StateEnum.TO).isValid()).toBe(true);
        });
        it('handles 11-digit rest == 2 edge case (dv forced to 9)', () => {
            expect(new IEValidation('00010000019', StateEnum.TO).isValid()).toBe(true);
        });
    });

    describe('wrong-length inputs (valid IE + extra digit)', () => {
        it('rejects AC with extra digit', () => {
            expect(new IEValidation('01519262310740', StateEnum.AC).isValid()).toBe(false);
        });
        it('rejects AL with extra digit', () => {
            expect(new IEValidation('2496157970', StateEnum.AL).isValid()).toBe(false);
        });
        it('rejects AM with extra digit', () => {
            expect(new IEValidation('0411627650', StateEnum.AM).isValid()).toBe(false);
        });
        it('rejects AP with extra digit', () => {
            expect(new IEValidation('0313484400', StateEnum.AP).isValid()).toBe(false);
        });
        it('rejects CE with extra digit', () => {
            expect(new IEValidation('2249011680', StateEnum.CE).isValid()).toBe(false);
        });
        it('rejects DF with extra digit', () => {
            expect(new IEValidation('07581075897250', StateEnum.DF).isValid()).toBe(false);
        });
        it('rejects ES with extra digit', () => {
            expect(new IEValidation('8980216500', StateEnum.ES).isValid()).toBe(false);
        });
        it('rejects GO with extra digit', () => {
            expect(new IEValidation('2096444190', StateEnum.GO).isValid()).toBe(false);
        });
        it('rejects MA with extra digit', () => {
            expect(new IEValidation('1221079850', StateEnum.MA).isValid()).toBe(false);
        });
        it('rejects MG with extra digit', () => {
            expect(new IEValidation('79089309325620', StateEnum.MG).isValid()).toBe(false);
        });
        it('rejects MS with extra digit', () => {
            expect(new IEValidation('2851645620', StateEnum.MS).isValid()).toBe(false);
        });
        it('rejects MT with extra digit', () => {
            expect(new IEValidation('496022251600', StateEnum.MT).isValid()).toBe(false);
        });
        it('rejects PA with extra digit', () => {
            expect(new IEValidation('1538049120', StateEnum.PA).isValid()).toBe(false);
        });
        it('rejects PB with extra digit', () => {
            expect(new IEValidation('8703378580', StateEnum.PB).isValid()).toBe(false);
        });
        it('rejects PI with extra digit', () => {
            expect(new IEValidation('3623984960', StateEnum.PI).isValid()).toBe(false);
        });
        it('rejects PR with extra digit', () => {
            expect(new IEValidation('56147140100', StateEnum.PR).isValid()).toBe(false);
        });
        it('rejects RJ with extra digit', () => {
            expect(new IEValidation('871448360', StateEnum.RJ).isValid()).toBe(false);
        });
        it('rejects RR with extra digit', () => {
            expect(new IEValidation('0408370430', StateEnum.RR).isValid()).toBe(false);
        });
        it('rejects RS with extra digit', () => {
            expect(new IEValidation('25562065670', StateEnum.RS).isValid()).toBe(false);
        });
        it('rejects SC with extra digit', () => {
            expect(new IEValidation('1442609130', StateEnum.SC).isValid()).toBe(false);
        });
        it('rejects SE with extra digit', () => {
            expect(new IEValidation('4961232890', StateEnum.SE).isValid()).toBe(false);
        });
        it('rejects SP with extra digit', () => {
            expect(new IEValidation('0000000100050', StateEnum.SP).isValid()).toBe(false);
        });
        it('rejects TO with extra digit (11→12)', () => {
            expect(new IEValidation('730331498200', StateEnum.TO).isValid()).toBe(false);
        });
        it('rejects RN with 11 digits (neither 9 nor 10)', () => {
            expect(new IEValidation('20492620190', StateEnum.RN).isValid()).toBe(false);
        });
    });

    describe('wrong-prefix inputs (valid DVs, wrong state prefix)', () => {
        it('rejects AC with prefix 02 instead of 01', () => {
            expect(new IEValidation('0200000000054', StateEnum.AC).isValid()).toBe(false);
        });
        it('rejects AL with prefix 25 instead of 24', () => {
            expect(new IEValidation('250000008', StateEnum.AL).isValid()).toBe(false);
        });
        it('rejects AM with prefix 05 instead of 04', () => {
            expect(new IEValidation('050000004', StateEnum.AM).isValid()).toBe(false);
        });
        it('rejects AP with prefix 02 instead of 03', () => {
            expect(new IEValidation('020000006', StateEnum.AP).isValid()).toBe(false);
        });
        it('rejects DF with prefix 08 instead of 07', () => {
            expect(new IEValidation('0800000000095', StateEnum.DF).isValid()).toBe(false);
        });
        it('rejects MA with prefix 13 instead of 12', () => {
            expect(new IEValidation('130000000', StateEnum.MA).isValid()).toBe(false);
        });
        it('rejects PA with prefix 16 instead of 15', () => {
            expect(new IEValidation('160000009', StateEnum.PA).isValid()).toBe(false);
        });
        it('rejects RN with prefix 21 instead of 20', () => {
            expect(new IEValidation('210000007', StateEnum.RN).isValid()).toBe(false);
        });
    });

    describe('unknown state', () => {
        it('throws InvalidStateRuleException for unknown state code', () => {
            expect(() => new IEValidation('123456789', 0)).toThrow(InvalidStateRuleException);
            expect(() => new IEValidation('123456789', 0)).toThrow('ie: unknown_uf');
        });
    });

    describe('whitelist / blacklist / validateOrFail', () => {
        it('whitelist() overrides invalid result', () => {
            expect(new IEValidation('209644418', StateEnum.GO).allowList(['209644418']).isValid()).toBe(true);
        });

        it('blacklist() overrides valid result', () => {
            expect(new IEValidation('209644419', StateEnum.GO).denyList(['209644419']).isValid()).toBe(false);
        });

        it('validateOrFail() returns true when valid', () => {
            expect(new IEValidation('209644419', StateEnum.GO).validateOrFail()).toBeUndefined();
        });

        it('validateOrFail() throws ValidationException when invalid', () => {
            expect(() => new IEValidation('209644418', StateEnum.GO).validateOrFail()).toThrow(ValidationException);
            expect(() => new IEValidation('209644418', StateEnum.GO).validateOrFail()).toThrow('ie: bad_check_digit');
        });

        it('validateOrFail() respects whitelist and blacklist', () => {
            expect(new IEValidation('209644418', StateEnum.GO).allowList(['209644418']).validateOrFail()).toBeUndefined();
            expect(() => new IEValidation('209644419', StateEnum.GO).denyList(['209644419']).validateOrFail()).toThrow(ValidationException);
        });
    });
});
