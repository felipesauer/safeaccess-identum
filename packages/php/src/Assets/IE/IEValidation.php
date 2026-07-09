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

    /**
     * Candidate digit lengths per state (IBGE code → lengths), used by {@see generate()}.
     * States whose format requires a fixed prefix are handled in {@see randomForState()}.
     *
     * @var array<int, list<int>>
     */
    private const GENERATE_LENGTHS = [
        StateEnum::RO->value => [9, 14],
        StateEnum::AC->value => [13],
        StateEnum::AM->value => [9],
        StateEnum::RR->value => [9],
        StateEnum::PA->value => [9],
        StateEnum::AP->value => [9],
        StateEnum::TO->value => [9, 11],
        StateEnum::MA->value => [9],
        StateEnum::PI->value => [9],
        StateEnum::CE->value => [9],
        StateEnum::RN->value => [9, 10],
        StateEnum::PB->value => [9],
        StateEnum::PE->value => [9, 14],
        StateEnum::AL->value => [9],
        StateEnum::SE->value => [9],
        StateEnum::BA->value => [8, 9],
        StateEnum::MG->value => [13],
        StateEnum::ES->value => [9],
        StateEnum::RJ->value => [8],
        StateEnum::SP->value => [12],
        StateEnum::PR->value => [10],
        StateEnum::SC->value => [9],
        StateEnum::RS->value => [10],
        StateEnum::MS->value => [9],
        StateEnum::MT->value => [11],
        StateEnum::GO->value => [9],
        StateEnum::DF->value => [13],
    ];

    /** Fixed leading digits some states require (IBGE code → prefix). */
    private const GENERATE_PREFIXES = [
        StateEnum::RN->value => '20',
    ];

    public function __construct(
        string $value,
        protected StateEnum|int $state,
    ) {
        parent::__construct($value);

        $this->doRule();
    }

    /**
     * Generates a valid IE for the given state.
     *
     * Uses rejection sampling: it draws random numbers of the state's valid
     * length(s) and keeps the first that passes {@see validate()}. This reuses the
     * already-tested per-state rules instead of reproducing all 27 algorithms.
     *
     * Intended for tests/fixtures, not a hot path. States with 13-digit formats
     * (AC, DF) have a sparser valid space and can take tens of milliseconds.
     */
    public static function generate(StateEnum|int $state): string
    {
        $code = $state instanceof StateEnum ? $state->value : $state;

        if (!array_key_exists($code, self::GENERATE_LENGTHS)) {
            throw new InvalidStateRuleException('ie', '');
        }

        $lengths = self::GENERATE_LENGTHS[$code];
        $prefix = self::GENERATE_PREFIXES[$code] ?? '';

        // Bounded attempts; the density of valid IEs is at worst ~1/1000, so this
        // converges well within the budget for every state.
        for ($attempt = 0; $attempt < 100000; $attempt++) {
            $length = $lengths[random_int(0, count($lengths) - 1)];
            $candidate = self::randomDigits($length, $prefix);

            if ((new self($candidate, $state))->doValidate() === null) {
                return $candidate;
            }
        }

        // Unreachable in practice for the shipped state rules.
        throw new InvalidStateRuleException('ie', ''); // @codeCoverageIgnore
    }

    /** A random numeric string of the given length, keeping any required prefix. */
    private static function randomDigits(int $length, string $prefix): string
    {
        $out = $prefix;
        for ($i = strlen($prefix); $i < $length; $i++) {
            $out .= random_int(0, 9);
        }

        return $out;
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
