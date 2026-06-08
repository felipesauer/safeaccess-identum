<?php

declare(strict_types=1);

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Exceptions\ValidationException;

/**
 * Builds an anonymous document whose domain result is fixed to $domain.
 *
 * `sanitize()` is overridden to the identity so blacklist/whitelist tests can
 * use plain non-numeric tokens ('x', 'foo') without them being stripped.
 */
function makeDoc(string $raw, bool $domain): AbstractValidatableDocument
{
    return new class ($raw, $domain) extends AbstractValidatableDocument {
        public function __construct(string $value, private bool $domain)
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

        protected function doValidate(): bool
        {
            return $this->domain;
        }
    };
}

describe(AbstractValidatableDocument::class, function () {

    it('returns raw value unchanged', function () {
        expect(makeDoc('  X-123  ', true)->raw())->toBe('  X-123  ');
    });

    it('validate() calls domain logic when lists are empty', function () {
        expect(makeDoc('ok', true)->validate())->toBeTrue()
            ->and(makeDoc('nope', false)->validate())->toBeFalse();
    });

    it('validate() returns true when raw is whitelisted (short-circuit)', function () {
        $Doc = makeDoc('x', false);
        $Doc->whitelist(['x']);
        expect($Doc->validate())->toBeTrue();
    });

    it('validate() returns false when raw is blacklisted (short-circuit)', function () {
        $Doc = makeDoc('y', true);
        $Doc->blacklist(['y']);
        expect($Doc->validate())->toBeFalse();
    });

    it('whitelist takes precedence over blacklist when both contain raw', function () {
        $Doc = makeDoc('z', false);
        $Doc->blacklist(['z'])->whitelist(['z']);
        expect($Doc->validate())->toBeTrue();
    });

    it('matches blacklist/whitelist after sanitizing both sides (format-agnostic)', function () {
        // Default sanitize strips non-digits, so masked input matches an unmasked list entry.
        $Doc = new class ('529.982.247-25') extends AbstractValidatableDocument {
            protected function documentName(): string
            {
                return 'test';
            }

            protected function doValidate(): bool
            {
                return false;
            }
        };

        expect($Doc->whitelist(['52998224725'])->validate())->toBeTrue();
    });

    it('validateOrFail() returns true when value is valid', function () {
        expect(makeDoc('ok', true)->validateOrFail())->toBeTrue();
    });

    it('validateOrFail() throws ValidationException prefixed with the document name', function () {
        expect(fn () => makeDoc('bad', false)->validateOrFail())
            ->toThrow(ValidationException::class, 'test: input invalid');
    });

    it('validateOrFail() returns true when raw is whitelisted', function () {
        $Doc = makeDoc('white', false);
        $Doc->whitelist(['white']);
        expect($Doc->validateOrFail())->toBeTrue();
    });

    it('validateOrFail() throws when raw is blacklisted even if domain would pass', function () {
        $Doc = makeDoc('black', true);
        $Doc->blacklist(['black']);
        expect(fn () => $Doc->validateOrFail())->toThrow(ValidationException::class);
    });

    it('blacklist() and whitelist() are chainable and override previous values', function () {
        $Doc = makeDoc('foo', false);

        // chain returns same instance
        $same = $Doc->blacklist(['a', 'b'])->whitelist(['foo']);
        expect($same)->toBe($Doc);

        // whitelist set last → valid
        expect($Doc->validate())->toBeTrue();

        // now override whitelist → remove foo
        $Doc->whitelist([]);
        expect($Doc->validate())->toBeFalse();

        // set blacklist to include foo → invalid independent of domain
        $Doc->blacklist(['foo']);
        expect($Doc->validate())->toBeFalse();
    });
});
