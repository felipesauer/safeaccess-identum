<?php

declare(strict_types=1);

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(AbstractValidatableDocument::class, function () {

    it('returns raw value unchanged', function () {
        $Doc = new class ('  X-123  ') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return true;
            }
        };

        expect($Doc->raw())->toBe('  X-123  ');
    });

    it('validate() calls domain logic when lists are empty', function () {
        // domain-valid
        $Valid = new class ('ok') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return true;
            }
        };
        // domain-invalid
        $Invalid = new class ('nope') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return false;
            }
        };

        expect($Valid->validate())->toBeTrue()
            ->and($Invalid->validate())->toBeFalse();
    });

    it('validate() returns true when raw is whitelisted (short-circuit)', function () {
        // domain would be false, but whitelist wins
        $Doc = new class ('x') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return false;
            }
        };

        $Doc->whitelist(['x']);
        expect($Doc->validate())->toBeTrue();
    });

    it('validate() returns false when raw is blacklisted (short-circuit)', function () {
        // domain would be true, but blacklist wins
        $Doc = new class ('y') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return true;
            }
        };

        $Doc->blacklist(['y']);
        expect($Doc->validate())->toBeFalse();
    });

    it('whitelist takes precedence over blacklist when both contain raw', function () {
        // even if domain would be false, whitelist must win
        $Doc = new class ('z') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return false;
            }
        };

        $Doc->blacklist(['z'])->whitelist(['z']); // whitelist depois
        expect($Doc->validate())->toBeTrue();
    });

    it('validateOrFail() returns true when value is valid', function () {
        $Doc = new class ('ok') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return true;
            }
        };

        expect($Doc->validateOrFail())->toBeTrue();
    });

    it('validateOrFail() throws ValidationException when value is invalid', function () {
        $Doc = new class ('bad') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return false;
            }
        };

        expect(fn () => $Doc->validateOrFail())->toThrow(ValidationException::class, 'input invalid');
    });

    it('validateOrFail() returns true when raw is whitelisted', function () {
        $Doc = new class ('white') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return false;
            }
        };

        $Doc->whitelist(['white']);
        expect($Doc->validateOrFail())->toBeTrue();
    });

    it('validateOrFail() throws when raw is blacklisted even if domain would pass', function () {
        $Doc = new class ('black') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return true;
            }
        };

        $Doc->blacklist(['black']);
        expect(fn () => $Doc->validateOrFail())->toThrow(ValidationException::class);
    });

    it('blacklist() and whitelist() are chainable and override previous values', function () {
        $Doc = new class ('foo') extends AbstractValidatableDocument {
            protected function doValidate(): bool
            {
                return false;
            }
        };

        // chain returns same instance
        $same = $Doc->blacklist(['a', 'b'])->whitelist(['foo']);
        expect($same)->toBe($Doc);

        // whitelist set last → valid
        expect($Doc->validate())->toBeTrue();

        // now override whitelist → remove foo
        $Doc->whitelist([]); // esvazia whitelist
        // keep blacklist empty; domain returns false → invalid
        expect($Doc->validate())->toBeFalse();

        // set blacklist to include foo → invalid independent of domain
        $Doc->blacklist(['foo']);
        expect($Doc->validate())->toBeFalse();
    });
});
