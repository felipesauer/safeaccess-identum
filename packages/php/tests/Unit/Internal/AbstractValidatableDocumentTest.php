<?php

declare(strict_types=1);

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\ReasonCode;
use SafeAccess\Identum\Exceptions\ValidationException;

/**
 * Builds an anonymous document whose domain result is fixed to $reason
 * (null = valid, otherwise the reason returned by doValidate()).
 *
 * `sanitize()` is overridden to the identity so deny/allow-list tests can
 * use plain non-numeric tokens ('x', 'foo') without them being stripped.
 */
function makeDoc(string $raw, ?ReasonCode $reason): AbstractValidatableDocument
{
    return new class ($raw, $reason) extends AbstractValidatableDocument {
        public function __construct(string $value, private ?ReasonCode $reason)
        {
            parent::__construct($value);
        }

        protected function documentName(): string
        {
            return 'test';
        }

        protected function sanitize(string $value): string
        {
            return $value;
        }

        protected function doValidate(): ?ReasonCode
        {
            return $this->reason;
        }
    };
}

describe(AbstractValidatableDocument::class, function () {

    it('returns raw value unchanged', function () {
        expect(makeDoc('  X-123  ', null)->raw())->toBe('  X-123  ');
    });

    it('validate() calls domain logic when lists are empty', function () {
        expect(makeDoc('ok', null)->validate()->valid)->toBeTrue()
            ->and(makeDoc('nope', ReasonCode::BadCheckDigit)->validate()->valid)->toBeFalse();
    });

    it('validate() surfaces the domain reason on failure', function () {
        expect(makeDoc('nope', ReasonCode::WrongLength)->validate()->reason)->toBe(ReasonCode::WrongLength);
    });

    it('isValid() is a boolean shortcut for validate().valid', function () {
        expect(makeDoc('ok', null)->isValid())->toBeTrue()
            ->and(makeDoc('nope', ReasonCode::BadCheckDigit)->isValid())->toBeFalse();
    });

    it('validate() returns valid when raw is allow-listed (short-circuit)', function () {
        expect(makeDoc('x', ReasonCode::BadCheckDigit)->allowList(['x'])->validate()->valid)->toBeTrue();
    });

    it('validate() returns denied when raw is deny-listed (short-circuit)', function () {
        $result = makeDoc('y', null)->denyList(['y'])->validate();
        expect($result->valid)->toBeFalse()
            ->and($result->reason)->toBe(ReasonCode::Denied);
    });

    it('allow list takes precedence over deny list when both contain raw', function () {
        expect(makeDoc('z', ReasonCode::BadCheckDigit)->denyList(['z'])->allowList(['z'])->validate()->valid)->toBeTrue();
    });

    it('matches deny/allow list after sanitizing both sides (format-agnostic)', function () {
        // Default sanitize strips non-digits, so masked input matches an unmasked list entry.
        $Doc = new class ('529.982.247-25') extends AbstractValidatableDocument {
            protected function documentName(): string
            {
                return 'test';
            }

            protected function doValidate(): ?ReasonCode
            {
                return ReasonCode::BadCheckDigit;
            }
        };

        expect($Doc->allowList(['52998224725'])->validate()->valid)->toBeTrue();
    });

    it('validateOrFail() returns void when value is valid', function () {
        expect(makeDoc('ok', null)->validateOrFail())->toBeNull();
    });

    it('validateOrFail() throws ValidationException carrying document + reason + normalized', function () {
        try {
            makeDoc('bad', ReasonCode::BadCheckDigit)->validateOrFail();
            test()->fail('expected ValidationException');
        } catch (ValidationException $e) {
            expect($e->document)->toBe('test')
                ->and($e->reason)->toBe(ReasonCode::BadCheckDigit)
                ->and($e->normalized)->toBe('bad')
                ->and($e->getMessage())->toBe('test: bad_check_digit');
        }
    });

    it('validateOrFail() returns void when raw is allow-listed', function () {
        expect(makeDoc('white', ReasonCode::BadCheckDigit)->allowList(['white'])->validateOrFail())->toBeNull();
    });

    it('validateOrFail() throws when raw is deny-listed even if domain would pass', function () {
        expect(fn () => makeDoc('black', null)->denyList(['black'])->validateOrFail())
            ->toThrow(ValidationException::class);
    });

    it('deny/allow lists are chainable and override previous values', function () {
        $Doc = makeDoc('foo', ReasonCode::BadCheckDigit);

        // chain returns same instance
        $same = $Doc->denyList(['a', 'b'])->allowList(['foo']);
        expect($same)->toBe($Doc);

        // allow list set last → valid
        expect($Doc->validate()->valid)->toBeTrue();

        // now override allow list → remove foo
        $Doc->allowList([]);
        expect($Doc->validate()->valid)->toBeFalse();

        // set deny list to include foo → invalid independent of domain
        $Doc->denyList(['foo']);
        expect($Doc->validate()->valid)->toBeFalse();
    });

    it('deprecated blacklist()/whitelist() delegate to deny/allow lists', function () {
        expect(makeDoc('x', ReasonCode::BadCheckDigit)->whitelist(['x'])->validate()->valid)->toBeTrue()
            ->and(makeDoc('y', null)->blacklist(['y'])->validate()->valid)->toBeFalse();
    });
});
