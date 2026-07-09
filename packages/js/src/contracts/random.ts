/**
 * @internal Random helpers shared by document generators.
 *
 * Intended for producing test fixtures — not a cryptographic source.
 */

/** Returns a random integer in the inclusive range [min, max]. */
export function randomInt(min: number, max: number): number {
    return min + Math.floor(Math.random() * (max - min + 1));
}

/** Returns a string of `length` random digits, keeping an optional fixed prefix. */
export function randomDigits(length: number, prefix = ''): string {
    let out = prefix;
    for (let i = prefix.length; i < length; i++) {
        out += randomInt(0, 9);
    }
    return out;
}
