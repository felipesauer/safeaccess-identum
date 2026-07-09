<?php

declare(strict_types=1);

namespace SafeAccess\Identum;

use SafeAccess\Identum\Assets\CEP\CEPValidation;
use SafeAccess\Identum\Assets\CNH\CNHValidation;
use SafeAccess\Identum\Assets\CNPJ\CNPJValidation;
use SafeAccess\Identum\Assets\CNS\CNSValidation;
use SafeAccess\Identum\Assets\CPF\CPFValidation;
use SafeAccess\Identum\Assets\IE\IEValidation;
use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Assets\PIS\PISValidation;
use SafeAccess\Identum\Assets\Plate\PlateMercosulValidation;
use SafeAccess\Identum\Assets\RENAVAM\RenavamValidation;
use SafeAccess\Identum\Assets\Voter\VoterTitleValidation;

/**
 * Main entry point for document validation.
 *
 * Exposes one concrete static factory per supported document type, mirroring
 * the TypeScript facade one-to-one.
 *
 * @api
 *
 * @see \SafeAccess\Identum\Contracts\ValidatableDocument Contract implemented by all validators.
 */
final class Identum
{
    public static function cpf(string $document): CPFValidation
    {
        return new CPFValidation($document);
    }

    public static function cnpj(string $document): CNPJValidation
    {
        return new CNPJValidation($document);
    }

    public static function cnh(string $document): CNHValidation
    {
        return new CNHValidation($document);
    }

    public static function cep(string $document): CEPValidation
    {
        return new CEPValidation($document);
    }

    public static function cns(string $document): CNSValidation
    {
        return new CNSValidation($document);
    }

    public static function pis(string $document): PISValidation
    {
        return new PISValidation($document);
    }

    public static function ie(string $document, StateEnum|int $state): IEValidation
    {
        return new IEValidation($document, $state);
    }

    public static function renavam(string $document): RenavamValidation
    {
        return new RenavamValidation($document);
    }

    public static function placa(string $document): PlateMercosulValidation
    {
        return new PlateMercosulValidation($document);
    }

    public static function tituloEleitor(string $document): VoterTitleValidation
    {
        return new VoterTitleValidation($document);
    }
}
