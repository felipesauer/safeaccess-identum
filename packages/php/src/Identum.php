<?php

declare(strict_types=1);

namespace SafeAccess\Identum;

use SafeAccess\Identum\Internal\AbstractResolver;

/**
 * Main entry point for document validation.
 *
 * Provides static aliases for all supported document validators,
 * resolved dynamically via the alias registry.
 *
 * @api
 *
 * @method static \SafeAccess\Identum\Assets\CEP\CEPValidation cep(string $document)
 * @method static \SafeAccess\Identum\Assets\CNH\CNHValidation cnh(string $document)
 * @method static \SafeAccess\Identum\Assets\CNPJ\CNPJValidation cnpj(string $document)
 * @method static \SafeAccess\Identum\Assets\CNS\CNSValidation cns(string $document)
 * @method static \SafeAccess\Identum\Assets\CPF\CPFValidation cpf(string $document)
 * @method static \SafeAccess\Identum\Assets\IE\IEValidation ie(string $document, \SafeAccess\Identum\Assets\IE\StateEnum|int $state)
 * @method static \SafeAccess\Identum\Assets\PIS\PISValidation pis(string $document)
 * @method static \SafeAccess\Identum\Assets\Plate\PlateMercosulValidation placa(string $document)
 * @method static \SafeAccess\Identum\Assets\RENAVAM\RenavamValidation renavam(string $document)
 * @method static \SafeAccess\Identum\Assets\Voter\VoterTitleValidation tituloEleitor(string $document)
 *
 * @see \SafeAccess\Identum\Contracts\ValidatableDocument Contract implemented by all validators.
 */
final class Identum extends AbstractResolver
{
    /**
     * @internal Prefer use via static aliases: Identum::cpf(...), Identum::cnpj(...), etc.
     */
    public function __construct()
    {
        parent::__construct([
            'cep' => \SafeAccess\Identum\Assets\CEP\CEPValidation::class,
            'cnh' => \SafeAccess\Identum\Assets\CNH\CNHValidation::class,
            'cnpj' => \SafeAccess\Identum\Assets\CNPJ\CNPJValidation::class,
            'cns' => \SafeAccess\Identum\Assets\CNS\CNSValidation::class,
            'cpf' => \SafeAccess\Identum\Assets\CPF\CPFValidation::class,
            'ie' => \SafeAccess\Identum\Assets\IE\IEValidation::class,
            'pis' => \SafeAccess\Identum\Assets\PIS\PISValidation::class,
            'placa' => \SafeAccess\Identum\Assets\Plate\PlateMercosulValidation::class,
            'renavam' => \SafeAccess\Identum\Assets\RENAVAM\RenavamValidation::class,
            'tituloEleitor' => \SafeAccess\Identum\Assets\Voter\VoterTitleValidation::class,
        ]);
    }
}
