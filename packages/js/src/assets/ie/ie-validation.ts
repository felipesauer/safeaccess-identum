import { AbstractValidatableDocumentRules } from '../../contracts/abstract-validatable-document-rules.js';
import { InvalidStateRuleException } from '../../exceptions/invalid-state-rule-exception.js';
import { StateEnum } from './state-enum.js';
import type { StateCode } from './state-enum.js';
import type { AbstractStateRule } from './abstract-state-rule.js';
import { RoRule } from './rules/ro-rule.js';
import { AcRule } from './rules/ac-rule.js';
import { AmRule } from './rules/am-rule.js';
import { RrRule } from './rules/rr-rule.js';
import { PaRule } from './rules/pa-rule.js';
import { ApRule } from './rules/ap-rule.js';
import { ToRule } from './rules/to-rule.js';
import { MaRule } from './rules/ma-rule.js';
import { PiRule } from './rules/pi-rule.js';
import { CeRule } from './rules/ce-rule.js';
import { RnRule } from './rules/rn-rule.js';
import { PbRule } from './rules/pb-rule.js';
import { PeRule } from './rules/pe-rule.js';
import { AlRule } from './rules/al-rule.js';
import { SeRule } from './rules/se-rule.js';
import { BaRule } from './rules/ba-rule.js';
import { MgRule } from './rules/mg-rule.js';
import { EsRule } from './rules/es-rule.js';
import { RjRule } from './rules/rj-rule.js';
import { SpRule } from './rules/sp-rule.js';
import { PrRule } from './rules/pr-rule.js';
import { ScRule } from './rules/sc-rule.js';
import { RsRule } from './rules/rs-rule.js';
import { MsRule } from './rules/ms-rule.js';
import { MtRule } from './rules/mt-rule.js';
import { GoRule } from './rules/go-rule.js';
import { DfRule } from './rules/df-rule.js';

type RuleFactory = () => AbstractStateRule;

/** @internal Rule registry mapping IBGE state codes to rule factories. */
const ruleMap: Record<number, RuleFactory> = {
    [StateEnum.RO]: () => new RoRule(),
    [StateEnum.AC]: () => new AcRule(),
    [StateEnum.AM]: () => new AmRule(),
    [StateEnum.RR]: () => new RrRule(),
    [StateEnum.PA]: () => new PaRule(),
    [StateEnum.AP]: () => new ApRule(),
    [StateEnum.TO]: () => new ToRule(),
    [StateEnum.MA]: () => new MaRule(),
    [StateEnum.PI]: () => new PiRule(),
    [StateEnum.CE]: () => new CeRule(),
    [StateEnum.RN]: () => new RnRule(),
    [StateEnum.PB]: () => new PbRule(),
    [StateEnum.PE]: () => new PeRule(),
    [StateEnum.AL]: () => new AlRule(),
    [StateEnum.SE]: () => new SeRule(),
    [StateEnum.BA]: () => new BaRule(),
    [StateEnum.MG]: () => new MgRule(),
    [StateEnum.ES]: () => new EsRule(),
    [StateEnum.RJ]: () => new RjRule(),
    [StateEnum.SP]: () => new SpRule(),
    [StateEnum.PR]: () => new PrRule(),
    [StateEnum.SC]: () => new ScRule(),
    [StateEnum.RS]: () => new RsRule(),
    [StateEnum.MS]: () => new MsRule(),
    [StateEnum.MT]: () => new MtRule(),
    [StateEnum.GO]: () => new GoRule(),
    [StateEnum.DF]: () => new DfRule(),
};

/**
 * Validates Brazilian IE (Inscrição Estadual) numbers.
 *
 * Dispatches to state-specific rules via {@link StateEnum} and the
 * corresponding {@link AbstractStateRule} implementation.
 *
 * @throws {@link InvalidStateRuleException} When the provided state code is not supported.
 */
export class IEValidation extends AbstractValidatableDocumentRules {
    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    private rule!: AbstractStateRule;
    private readonly state: StateCode | number;

    constructor(value: string, state: StateCode | number) {
        super(value);
        this.state = state;
        this.doRule();
    }

    protected doRule(): this {
        const factory = ruleMap[this.state];
        if (!factory) {
            throw new InvalidStateRuleException('invalid state rule');
        }

        this.rule = factory();

        return this;
    }

    protected doValidate(): boolean {
        return this.rule.execute(this._raw);
    }
}
