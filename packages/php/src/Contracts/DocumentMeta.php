<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

/**
 * Metadata extracted from a valid document, offline, from the number itself.
 *
 * Every field is nullable and only populated by validators for which it is
 * meaningful (e.g. {@see self::$uf} for CPF/IE, {@see self::$type} for CNS).
 * A validator that has nothing to extract returns `null` instead of an
 * all-null instance (see {@see ValidationResult::$meta}).
 *
 * The field set mirrors the TypeScript `DocumentMeta` shape for parity.
 *
 * @api
 */
final readonly class DocumentMeta
{
    public function __construct(
        /** Federative unit (state), e.g. 'SP' — CPF (fiscal region) and IE. */
        public ?string $uf = null,
        /** Document subtype, e.g. CNS 'definitive'/'provisional', certidão 'birth'/'marriage'/'death'. */
        public ?string $type = null,
        /** Card brand inferred from the BIN (best-effort), e.g. 'visa'. */
        public ?string $brand = null,
        /** PIX key type, e.g. 'cpf', 'cnpj', 'email', 'phone', 'evp'. */
        public ?string $keyType = null,
        /** CNPJ: whether it is a headquarters (matriz) rather than a branch. */
        public ?bool $isMatriz = null,
        /** CNPJ: whether the number uses the alphanumeric format. */
        public ?bool $isAlphanumeric = null,
        /** Plate layout, e.g. 'mercosul' or 'old'. */
        public ?string $pattern = null,
    ) {
    }
}
