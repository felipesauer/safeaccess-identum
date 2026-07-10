<?php

declare(strict_types=1);

use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Identum;

/*
 * Cross-language parity tests.
 *
 * The canonical ground truth lives in the repository-root `parity-vectors.json`,
 * loaded by BOTH packages — this file and packages/js/tests/parity.test.ts.
 * Add or change cases ONLY in that JSON so PHP and TypeScript can never drift.
 */

/**
 * Loads the shared parity vectors from the repository root.
 *
 * @return array<string, list<array{input: string, valid: bool, state?: string, reason?: string|null}>>
 */
function parityVectors(): array
{
    $path = dirname(__DIR__, 4) . '/parity-vectors.json';
    $json = file_get_contents($path);

    if ($json === false) {
        throw new RuntimeException("Unable to read parity vectors at {$path}");
    }

    /** @var array<string, list<array{input: string, valid: bool, state?: string, reason?: string|null}>> $data */
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    return $data;
}

$vectors = parityVectors();

$verb = fn (bool $valid): string => $valid ? 'accepts' : 'rejects';

describe('Parity — same input, same output in PHP and TypeScript', function () use ($vectors, $verb) {

    $simple = [
        'CPF' => 'cpf',
        'CNPJ' => 'cnpj',
        'CNH' => 'cnh',
        'CEP' => 'cep',
        'CNS' => 'cns',
        'PIS' => 'pis',
        'RENAVAM' => 'renavam',
        'Plate (Mercosul)' => 'placa',
        'Voter Title' => 'tituloEleitor',
        'Certidão' => 'certidao',
        'Cartão' => 'cartao',
        'PIX' => 'pix',
    ];

    foreach ($simple as $label => $alias) {
        describe($label, function () use ($vectors, $alias, $verb) {
            foreach ($vectors[$alias] as $case) {
                it("{$verb($case['valid'])} {$case['input']}", function () use ($alias, $case) {
                    $result = Identum::$alias($case['input'])->validate();
                    expect($result->valid)->toBe($case['valid']);
                    if (array_key_exists('reason', $case)) {
                        expect($result->reason?->value)->toBe($case['reason']);
                    }
                });
            }
        });
    }

    describe('IE', function () use ($vectors, $verb) {
        foreach ($vectors['ie'] as $case) {
            it("{$verb($case['valid'])} {$case['input']} ({$case['state']})", function () use ($case) {
                /** @var StateEnum $state */
                $state = constant(StateEnum::class . '::' . $case['state']);
                $result = Identum::ie($case['input'], $state)->validate();
                expect($result->valid)->toBe($case['valid']);
                if (array_key_exists('reason', $case)) {
                    expect($result->reason?->value)->toBe($case['reason']);
                }
            });
        }
    });
});
