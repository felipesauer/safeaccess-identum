<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\CPF\CPFValidation;
use SafeAccess\Identum\Contracts\ReasonCode;
use SafeAccess\Identum\Exceptions\ValidationException;

describe(CPFValidation::class, function () {

    it('validates CPF (masked and unmasked) as valid', function () {
        expect((new CPFValidation('864.600.120-24'))->isValid())->toBeTrue();
        expect((new CPFValidation('71031347070'))->isValid())->toBeTrue();
        expect((new CPFValidation('93011581088'))->isValid())->toBeTrue();
        expect((new CPFValidation('745.508.470-69'))->isValid())->toBeTrue();
    });

    it('returns a rich result on success (normalized + fiscal-region meta, no reason)', function () {
        $result = (new CPFValidation('864.600.120-24'))->validate();

        expect($result->valid)->toBeTrue();
        expect($result->reason)->toBeNull();
        expect($result->normalized)->toBe('86460012024');
        expect($result->meta?->uf)->toBe('RS'); // 9th digit (index 8) is 0 -> fiscal region RS
    });

    it('meta has the full fixed shape (all fields present, non-applicable ones null) for JS parity', function () {
        $meta = (new CPFValidation('864.600.120-24'))->validate()->meta;

        expect($meta->uf)->toBe('RS')
            ->and($meta->type)->toBeNull()
            ->and($meta->brand)->toBeNull()
            ->and($meta->keyType)->toBeNull()
            ->and($meta->isMatriz)->toBeNull()
            ->and($meta->isAlphanumeric)->toBeNull()
            ->and($meta->pattern)->toBeNull();
    });

    it('allow-listed values are valid but carry no metadata (meta is null)', function () {
        // The value was force-accepted, not proven a real document, so no meta is extracted
        // (and this must not trip the extractMeta index access on a short value).
        $result = (new CPFValidation('123'))->allowList(['123'])->validate();
        expect($result->valid)->toBeTrue()
            ->and($result->meta)->toBeNull();
    });

    it('rejects CPF with wrong check digits (DV) with reason bad_check_digit', function () {
        $result = (new CPFValidation('323.543.123-43'))->validate();
        expect($result->valid)->toBeFalse();
        expect($result->reason)->toBe(ReasonCode::BadCheckDigit);

        expect((new CPFValidation('98273487634'))->validate()->reason)->toBe(ReasonCode::BadCheckDigit);
    });

    it('rejects CPF with wrong length with reason wrong_length', function () {
        expect((new CPFValidation('9999999999'))->validate()->reason)->toBe(ReasonCode::WrongLength);
        expect((new CPFValidation('123456789012'))->validate()->reason)->toBe(ReasonCode::WrongLength);
    });

    it('rejects CPF made of repeated digits with reason known_invalid', function () {
        expect((new CPFValidation('00000000000'))->validate()->reason)->toBe(ReasonCode::KnownInvalid);
        expect((new CPFValidation('11111111111'))->validate()->reason)->toBe(ReasonCode::KnownInvalid);
        expect((new CPFValidation('222.222.222-22'))->validate()->reason)->toBe(ReasonCode::KnownInvalid);
    });

    it('rejects empty or non-sense strings with reason wrong_length', function () {
        expect((new CPFValidation(''))->validate()->reason)->toBe(ReasonCode::WrongLength);
        expect((new CPFValidation('   '))->validate()->reason)->toBe(ReasonCode::WrongLength);
    });

    it('ignores non-digit characters before validating', function () {
        expect((new CPFValidation('864.600.120-24'))->isValid())->toBeTrue();
        expect((new CPFValidation("  864 600-120..24 \n\t"))->isValid())->toBeTrue();
        expect((new CPFValidation('86460012024'))->isValid())->toBeTrue();
    });

    it('allowList() overrides invalid result', function () {
        expect((new CPFValidation('323.543.123-43'))->allowList(['323.543.123-43'])->isValid())->toBeTrue();
    });

    it('allowList()/denyList() match regardless of formatting (both sides sanitized)', function () {
        // masked input vs unmasked list entry
        expect((new CPFValidation('323.543.123-43'))->allowList(['32354312343'])->isValid())->toBeTrue();
        // unmasked input vs masked list entry
        $denied = (new CPFValidation('86460012024'))->denyList(['864.600.120-24'])->validate();
        expect($denied->valid)->toBeFalse();
        expect($denied->reason)->toBe(ReasonCode::Denied);
    });

    it('denyList() overrides valid result with reason denied', function () {
        $result = (new CPFValidation('864.600.120-24'))->denyList(['864.600.120-24'])->validate();
        expect($result->valid)->toBeFalse();
        expect($result->reason)->toBe(ReasonCode::Denied);
    });

    it('deprecated whitelist()/blacklist() still work as aliases', function () {
        expect((new CPFValidation('323.543.123-43'))->whitelist(['323.543.123-43'])->isValid())->toBeTrue();
        expect((new CPFValidation('864.600.120-24'))->blacklist(['864.600.120-24'])->isValid())->toBeFalse();
    });

    it('validateOrFail() returns void when valid', function () {
        expect((new CPFValidation('864.600.120-24'))->validateOrFail())->toBeNull();
    });

    it('validateOrFail() throws ValidationException carrying structured context', function () {
        try {
            (new CPFValidation('323.543.123-43'))->validateOrFail();
            $this->fail('expected ValidationException');
        } catch (ValidationException $e) {
            expect($e->document)->toBe('cpf');
            expect($e->reason)->toBe(ReasonCode::BadCheckDigit);
            expect($e->normalized)->toBe('32354312343');
            expect($e->getMessage())->toBe('cpf: bad_check_digit');
        }
    });

    it('validateOrFail() respects allowList and denyList', function () {
        expect((new CPFValidation('323.543.123-43'))->allowList(['323.543.123-43'])->validateOrFail())->toBeNull();
        expect(fn () => (new CPFValidation('864.600.120-24'))->denyList(['864.600.120-24'])->validateOrFail())
            ->toThrow(ValidationException::class);
    });

    it('strip() returns the canonical unformatted value', function () {
        expect((new CPFValidation('529.982.247-25'))->strip())->toBe('52998224725');
        expect((new CPFValidation('52998224725'))->strip())->toBe('52998224725');
    });

    it('format() applies the canonical mask', function () {
        expect((new CPFValidation('52998224725'))->format())->toBe('529.982.247-25');
        expect((new CPFValidation('529.982.247-25'))->format())->toBe('529.982.247-25');
    });

    it('format() is best-effort: returns the stripped value when it does not fit the mask', function () {
        expect((new CPFValidation('529'))->format())->toBe('529');
        expect((new CPFValidation('529982247250000'))->format())->toBe('529982247250000');
    });
});
