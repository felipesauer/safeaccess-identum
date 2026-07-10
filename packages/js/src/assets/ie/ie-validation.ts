import { AbstractValidatableDocumentRules } from '../../contracts/abstract-validatable-document-rules.js';
import { ReasonCode } from '../../contracts/reason-code.js';
import { documentMeta, type DocumentMeta } from '../../contracts/validation-result.js';
import { randomInt, randomDigits } from '../../contracts/random.js';
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
/**
 * Candidate digit lengths per state (IBGE code → lengths), used by generate().
 * States whose format requires a fixed prefix are listed in GENERATE_PREFIXES.
 */
const GENERATE_LENGTHS: Record<number, number[]> = {
    [StateEnum.RO]: [9, 14],
    [StateEnum.AC]: [13],
    [StateEnum.AM]: [9],
    [StateEnum.RR]: [9],
    [StateEnum.PA]: [9],
    [StateEnum.AP]: [9],
    [StateEnum.TO]: [9, 11],
    [StateEnum.MA]: [9],
    [StateEnum.PI]: [9],
    [StateEnum.CE]: [9],
    [StateEnum.RN]: [9, 10],
    [StateEnum.PB]: [9],
    [StateEnum.PE]: [9, 14],
    [StateEnum.AL]: [9],
    [StateEnum.SE]: [9],
    [StateEnum.BA]: [8, 9],
    [StateEnum.MG]: [13],
    [StateEnum.ES]: [9],
    [StateEnum.RJ]: [8],
    [StateEnum.SP]: [12],
    [StateEnum.PR]: [10],
    [StateEnum.SC]: [9],
    [StateEnum.RS]: [10],
    [StateEnum.MS]: [9],
    [StateEnum.MT]: [11],
    [StateEnum.GO]: [9],
    [StateEnum.DF]: [13],
};

/** Fixed leading digits some states require (IBGE code → prefix). */
const GENERATE_PREFIXES: Record<number, string> = {
    [StateEnum.RN]: '20',
};

export class IEValidation extends AbstractValidatableDocumentRules {
    private rule!: AbstractStateRule;
    private readonly state: StateCode | number;

    constructor(value: string, state: StateCode | number) {
        super(value);
        this.state = state;
        this.doRule();
    }

    /**
     * Generates a valid IE for the given state.
     *
     * Uses rejection sampling: it draws random numbers of the state's valid
     * length(s) and keeps the first that passes validate(). This reuses the
     * already-tested per-state rules instead of reproducing all 27 algorithms.
     *
     * Intended for tests/fixtures, not a hot path. States with 13-digit formats
     * (AC, DF) have a sparser valid space and can take longer.
     */
    static generate(state: StateCode | number): string {
        const lengths = GENERATE_LENGTHS[state];
        if (!lengths) {
            throw new InvalidStateRuleException('ie', '');
        }

        const prefix = GENERATE_PREFIXES[state] ?? '';

        for (let attempt = 0; attempt < 100000; attempt++) {
            const length = lengths[randomInt(0, lengths.length - 1)];
            const candidate = randomDigits(length, prefix);

            if (new IEValidation(candidate, state).validate().valid) {
                return candidate;
            }
        }

        // Unreachable in practice for the shipped state rules.
        /* c8 ignore next */
        throw new InvalidStateRuleException('ie', '');
    }

    protected doRule(): this {
        const factory = ruleMap[this.state];
        if (!factory) {
            throw new InvalidStateRuleException('ie', this.sanitize(this._raw));
        }

        this.rule = factory();

        return this;
    }

    protected documentName(): string {
        return 'ie';
    }

    protected doValidate(): ReasonCode | null {
        return this.rule.execute(this._raw) ? null : ReasonCode.BadCheckDigit;
    }

    /** The UF is known upfront (it is the dispatch key), so echo it back as metadata. */
    protected extractMeta(): DocumentMeta {
        // The state was validated in the constructor (doRule), so a name always exists.
        const uf = Object.keys(StateEnum).find(
            (name) => StateEnum[name as keyof typeof StateEnum] === this.state,
        );

        return documentMeta({ uf: uf ?? null });
    }
}
