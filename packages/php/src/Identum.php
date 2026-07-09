<?php

declare(strict_types=1);

namespace SafeAccess\Identum;

use SafeAccess\Identum\Assets\Cartao\CartaoValidation;
use SafeAccess\Identum\Assets\CEP\CEPValidation;
use SafeAccess\Identum\Assets\Certidao\CertidaoValidation;
use SafeAccess\Identum\Assets\CNH\CNHValidation;
use SafeAccess\Identum\Assets\CNPJ\CNPJValidation;
use SafeAccess\Identum\Assets\CNS\CNSValidation;
use SafeAccess\Identum\Assets\CPF\CPFValidation;
use SafeAccess\Identum\Assets\IE\IEValidation;
use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Assets\PIS\PISValidation;
use SafeAccess\Identum\Assets\Pix\PixValidation;
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

    /** Generates a valid CPF (unmasked by default). */
    public static function generateCpf(bool $formatted = false): string
    {
        return CPFValidation::generate($formatted);
    }

    public static function cnpj(string $document): CNPJValidation
    {
        return new CNPJValidation($document);
    }

    /** Generates a valid (numeric) CNPJ (unmasked by default). */
    public static function generateCnpj(bool $formatted = false): string
    {
        return CNPJValidation::generate($formatted);
    }

    public static function cnh(string $document): CNHValidation
    {
        return new CNHValidation($document);
    }

    /** Generates a valid CNH. */
    public static function generateCnh(): string
    {
        return CNHValidation::generate();
    }

    public static function cartao(string $document): CartaoValidation
    {
        return new CartaoValidation($document);
    }

    public static function cep(string $document): CEPValidation
    {
        return new CEPValidation($document);
    }

    /** Generates a valid CEP (unmasked by default). */
    public static function generateCep(bool $formatted = false): string
    {
        return CEPValidation::generate($formatted);
    }

    public static function certidao(string $document): CertidaoValidation
    {
        return new CertidaoValidation($document);
    }

    public static function cns(string $document): CNSValidation
    {
        return new CNSValidation($document);
    }

    /** Generates a valid CNS (provisional type; unmasked by default). */
    public static function generateCns(bool $formatted = false): string
    {
        return CNSValidation::generate($formatted);
    }

    public static function pis(string $document): PISValidation
    {
        return new PISValidation($document);
    }

    /** Generates a valid PIS/PASEP (unmasked by default). */
    public static function generatePis(bool $formatted = false): string
    {
        return PISValidation::generate($formatted);
    }

    public static function pix(string $key): PixValidation
    {
        return new PixValidation($key);
    }

    public static function ie(string $document, StateEnum|int $state): IEValidation
    {
        return new IEValidation($document, $state);
    }

    /** Generates a valid IE for the given state (unmasked). */
    public static function generateIe(StateEnum|int $state): string
    {
        return IEValidation::generate($state);
    }

    public static function renavam(string $document): RenavamValidation
    {
        return new RenavamValidation($document);
    }

    /** Generates a valid RENAVAM. */
    public static function generateRenavam(): string
    {
        return RenavamValidation::generate();
    }

    public static function placa(string $document): PlateMercosulValidation
    {
        return new PlateMercosulValidation($document);
    }

    /** Generates a valid Mercosul plate. */
    public static function generatePlaca(): string
    {
        return PlateMercosulValidation::generate();
    }

    public static function tituloEleitor(string $document): VoterTitleValidation
    {
        return new VoterTitleValidation($document);
    }

    /** Generates a valid Voter Title. */
    public static function generateTituloEleitor(): string
    {
        return VoterTitleValidation::generate();
    }
}
