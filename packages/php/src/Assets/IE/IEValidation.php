<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE;

use SafeAccess\Identum\Contracts\AbstractValidatableDocumentRules;
use SafeAccess\Identum\Contracts\DocumentMeta;
use SafeAccess\Identum\Contracts\ReasonCode;
use SafeAccess\Identum\Exceptions\InvalidStateRuleException;

/**
 * Validates Brazilian IE (Inscrição Estadual) numbers.
 *
 * Dispatches to state-specific rules via {@see StateEnum} and the
 * corresponding {@see AbstractStateRule} implementation.
 *
 * @api
 *
 * @see StateEnum         Enum of valid Brazilian state codes.
 * @see AbstractStateRule Base class for per-state validation rules.
 *
 * @throws InvalidStateRuleException When the provided state code is not supported.
 */
final class IEValidation extends AbstractValidatableDocumentRules
{
    /**
     * @var array<int, class-string<AbstractStateRule>>
     */
    protected array $alias = [
        StateEnum::RO->value => \SafeAccess\Identum\Assets\IE\Rules\RoRule::class,
        StateEnum::AC->value => \SafeAccess\Identum\Assets\IE\Rules\AcRule::class,
        StateEnum::AM->value => \SafeAccess\Identum\Assets\IE\Rules\AmRule::class,
        StateEnum::RR->value => \SafeAccess\Identum\Assets\IE\Rules\RrRule::class,
        StateEnum::PA->value => \SafeAccess\Identum\Assets\IE\Rules\PaRule::class,
        StateEnum::AP->value => \SafeAccess\Identum\Assets\IE\Rules\ApRule::class,
        StateEnum::TO->value => \SafeAccess\Identum\Assets\IE\Rules\ToRule::class,
        StateEnum::MA->value => \SafeAccess\Identum\Assets\IE\Rules\MaRule::class,
        StateEnum::PI->value => \SafeAccess\Identum\Assets\IE\Rules\PiRule::class,
        StateEnum::CE->value => \SafeAccess\Identum\Assets\IE\Rules\CeRule::class,
        StateEnum::RN->value => \SafeAccess\Identum\Assets\IE\Rules\RnRule::class,
        StateEnum::PB->value => \SafeAccess\Identum\Assets\IE\Rules\PbRule::class,
        StateEnum::PE->value => \SafeAccess\Identum\Assets\IE\Rules\PeRule::class,
        StateEnum::AL->value => \SafeAccess\Identum\Assets\IE\Rules\AlRule::class,
        StateEnum::SE->value => \SafeAccess\Identum\Assets\IE\Rules\SeRule::class,
        StateEnum::BA->value => \SafeAccess\Identum\Assets\IE\Rules\BaRule::class,
        StateEnum::MG->value => \SafeAccess\Identum\Assets\IE\Rules\MgRule::class,
        StateEnum::ES->value => \SafeAccess\Identum\Assets\IE\Rules\EsRule::class,
        StateEnum::RJ->value => \SafeAccess\Identum\Assets\IE\Rules\RjRule::class,
        StateEnum::SP->value => \SafeAccess\Identum\Assets\IE\Rules\SpRule::class,
        StateEnum::PR->value => \SafeAccess\Identum\Assets\IE\Rules\PrRule::class,
        StateEnum::SC->value => \SafeAccess\Identum\Assets\IE\Rules\ScRule::class,
        StateEnum::RS->value => \SafeAccess\Identum\Assets\IE\Rules\RsRule::class,
        StateEnum::MS->value => \SafeAccess\Identum\Assets\IE\Rules\MsRule::class,
        StateEnum::MT->value => \SafeAccess\Identum\Assets\IE\Rules\MtRule::class,
        StateEnum::GO->value => \SafeAccess\Identum\Assets\IE\Rules\GoRule::class,
        StateEnum::DF->value => \SafeAccess\Identum\Assets\IE\Rules\DfRule::class,
    ];

    /**
     * @var class-string<AbstractStateRule>
     */
    protected string $rule;

    public function __construct(
        string $value,
        protected StateEnum|int $state,
    ) {
        parent::__construct($value);

        $this->doRule();
    }

    /**
     * {@inheritDoc}
     */
    protected function doRule(): static
    {
        $state = $this->state instanceof StateEnum ? $this->state->value : $this->state;

        if (!array_key_exists($state, $this->alias)) {
            throw new InvalidStateRuleException('ie', $this->sanitize($this->raw));
        }

        $this->rule = $this->alias[$state];

        return $this;
    }

    protected function documentName(): string
    {
        return 'ie';
    }

    /**
     * {@inheritDoc}
     */
    protected function doValidate(): ?ReasonCode
    {
        $rule = new ($this->rule)();

        return $rule->execute($this->raw) ? null : ReasonCode::BadCheckDigit;
    }

    /** The UF is known upfront (it is the dispatch key), so echo it back as metadata. */
    protected function extractMeta(string $normalized): DocumentMeta
    {
        $state = $this->state instanceof StateEnum ? $this->state : StateEnum::from($this->state);

        return new DocumentMeta(uf: $state->name);
    }
}
