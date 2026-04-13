<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE;

/**
 * Enumerate all Brazilian states by their Federal Tax Office (IBGE) code.
 *
 * Used by {@see IEValidation} to dispatch validation to the corresponding
 * state-specific rule implementation.
 *
 * @api
 *
 * @see IEValidation      Validator that uses this enum for state dispatch.
 * @see AbstractStateRule Base class for per-state validation rules.
 */
enum StateEnum: int
{
    /** Rondônia. */
    case RO = 11;

    /** Acre. */
    case AC = 12;

    /** Amazonas. */
    case AM = 13;

    /** Roraima. */
    case RR = 14;

    /** Pará. */
    case PA = 15;

    /** Amapá. */
    case AP = 16;

    /** Tocantins. */
    case TO = 17;

    /** Maranhão. */
    case MA = 21;

    /** Piauí. */
    case PI = 22;

    /** Ceará. */
    case CE = 23;

    /** Rio Grande do Norte. */
    case RN = 24;

    /** Paraíba. */
    case PB = 25;

    /** Pernambuco. */
    case PE = 26;

    /** Alagoas. */
    case AL = 27;

    /** Sergipe. */
    case SE = 28;

    /** Bahia. */
    case BA = 29;

    /** Minas Gerais. */
    case MG = 31;

    /** Espírito Santo. */
    case ES = 32;

    /** Rio de Janeiro. */
    case RJ = 33;

    /** São Paulo. */
    case SP = 35;

    /** Paraná. */
    case PR = 41;

    /** Santa Catarina. */
    case SC = 42;

    /** Rio Grande do Sul. */
    case RS = 43;

    /** Mato Grosso do Sul. */
    case MS = 50;

    /** Mato Grosso. */
    case MT = 51;

    /** Goiás. */
    case GO = 52;

    /** Distrito Federal. */
    case DF = 53;
}
